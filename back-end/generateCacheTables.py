import sqlite3

db_file = 'data/rulemaking_influence.db'
tableNames = [
"cache_home_page",
"cache_frdocs_page",
"cache_comment_page",
"cache_responses_page",
"cache_org_page",
"cache_org_agency",
"cache_org_doc_changes",
]

tablesToDrop = tableNames
# tablesToDrop = ["cache_org_page"]
try:
    connection = sqlite3.connect(db_file)

    cursor = connection.cursor()
    for tableName in tablesToDrop:
        cursor.execute(f"DROP TABLE IF EXISTS {tableName}")
        print(f"Dropped cached table {tableName}")

    cursor.execute("""CREATE INDEX IF NOT EXISTS comment_id_index_frdoc_comments ON frdoc_comments (comment_id)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS comment_id_index ON comment_responses (comment_id)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS frdoc_response_id_index_comment_responses ON comment_responses (frdoc_number, response_id)""")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_home_page AS
                      SELECT DISTINCT
                      org_name,
                      SUM(y_prob>0.5) AS y_count,
                      COUNT(DISTINCT(org_responses.frdoc_number)) as n_frdocs
                      FROM org_responses
                      INNER JOIN responses
                      ON (responses.frdoc_number==org_responses.frdoc_number)
                      AND (responses.response_id==org_responses.response_id)
                      GROUP BY org_name
                      ORDER BY -y_count""")

    connection.commit()

    print("Created home page table")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_org_page AS
                    SELECT A.org_name, number_of_comments, y_prob_avg, total_response_count, total_rules_changed
                    FROM ( -- Counts per org
                        SELECT comment_orgs.org_name, SUM(comments_and_responses.response_count) AS total_response_count, coalesce(SUM(CASE WHEN comments_and_responses.number_of_changes > 0 THEN 1 ELSE 0 END), 0) AS total_rules_changed
                        FROM comment_orgs
                        LEFT JOIN (
                            SELECT comment_id, coalesce(COUNT(DISTINCT(comment_responses.response_id)), 0) AS response_count, SUM(CASE WHEN y_prob > 0.5 THEN 1 ELSE 0 END) AS number_of_changes
                            FROM comment_responses
                            LEFT JOIN responses ON comment_responses.frdoc_number=responses.frdoc_number AND comment_responses.response_id=responses.response_id
                            GROUP BY comment_id
                        ) comments_and_responses ON comment_orgs.comment_id=comments_and_responses.comment_id
                        GROUP BY org_name
                    ) A
                    LEFT JOIN ( -- Averages per org
                        SELECT comment_orgs.org_name, AVG(responses.y_prob) AS y_prob_avg, COUNT(DISTINCT(comment_orgs.comment_id)) AS number_of_comments
                        FROM comment_orgs
                        LEFT JOIN comment_responses ON comment_orgs.comment_id=comment_responses.comment_id
                        LEFT JOIN responses ON comment_responses.frdoc_number=responses.frdoc_number AND comment_responses.response_id=responses.response_id
                        GROUP BY org_name
                    ) B ON A.org_name=B.org_name
                    LEFT JOIN (
                        SELECT org_name AS count_org_name, COUNT(DISTINCT(comment_id))
                        FROM comment_orgs
                        GROUP BY org_name
                    ) ON A.org_name=count_org_name""")

    connection.commit()

    print("Created org page table")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_frdocs_page AS
                  SELECT json_group_array(DISTINCT(agency)) as agencies,
                         publication_date,
                         fr_type,
                         type,
                         frdocs.frdoc_number,
                         title,
                         abstract,
                         action,
                         JSON_GROUP_ARRAY(DISTINCT prevSeq.frdoc_i) as prevFRDoc,
                         JSON_GROUP_ARRAY(DISTINCT nextSeq.frdoc_j) as nextFRDoc,
                         COALESCE(responseCount, 0) as response_count,
                         COALESCE(commentCount, 0) as comment_count,
                         COALESCE(change_count, 0) as change_count
                  FROM frdocs
                           LEFT JOIN frdoc_agencies ON frdocs.frdoc_number=frdoc_agencies.frdoc_number
                           LEFT JOIN frdoc_sequences prevSeq ON frdocs.frdoc_number = prevSeq.frdoc_j
                           LEFT JOIN frdoc_sequences nextSeq ON frdocs.frdoc_number = nextSeq.frdoc_i
                           LEFT JOIN (SELECT frdoc_number, COUNT(*) as responseCount, SUM(CASE WHEN y_prob>0.5 THEN 1 ELSE 0 END) as change_count FROM responses GROUP BY frdoc_number) responses ON frdocs.frdoc_number = responses.frdoc_number
                           LEFT JOIN (SELECT frdoc_number, COUNT(*) as commentCount FROM responses GROUP BY frdoc_number) comments ON frdocs.frdoc_number = comments.frdoc_number
                  GROUP BY frdocs.frdoc_number;""")

    cursor.execute("""CREATE INDEX IF NOT EXISTS frdoc_number_index ON cache_frdocs_page (frdoc_number)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS date_index ON cache_frdocs_page (publication_date)""")

    connection.commit()

    print("Created cache_frdocs_page")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_comment_page AS
                      SELECT cr.frdoc_number, fc.comment_id, fr.title, fc.receive_date,
                             COALESCE(COUNT(cr.response_id), 0) AS linked_responses,
                             SUM(COALESCE(cr.score, 0) > 0.5) AS number_of_changes,
                             JSON_GROUP_ARRAY(DISTINCT org_name) AS orgs, JSON_GROUP_ARRAY(DISTINCT agency) AS agencies
                      FROM frdoc_comments fc
                               LEFT JOIN comment_responses cr ON fc.comment_id = cr.comment_id
                               LEFT JOIN comment_orgs co ON fc.comment_id = co.comment_id
                               LEFT JOIN frdoc_agencies fa ON fc.frdoc_number = fa.frdoc_number
                               LEFT JOIN frdocs fr ON fc.frdoc_number = fr.frdoc_number
                      GROUP BY fc.frdoc_number, fc.comment_id""")

    cursor.execute("""CREATE INDEX IF NOT EXISTS linked_responses ON cache_comment_page (linked_responses)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS number_of_changes ON cache_comment_page (number_of_changes)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS receive_date_index ON cache_comment_page (receive_date)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS comment_id_index_cache_comment_page ON cache_comment_page (comment_id)""")

    connection.commit()

    print("Created comments table")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_responses_page AS
                      SELECT r.frdoc_number,
                             r.response_id,
                             r.any_change,
                             COUNT(cr.comment_id) as number_of_comments,
                             fr.title,
                             JSON_GROUP_ARRAY(rp.text) as text
                      FROM responses r
                      JOIN comment_responses cr ON r.response_id=cr.response_id AND r.frdoc_number=cr.frdoc_number
                      JOIN response_paragraphs rp on r.response_id=rp.response_id AND r.frdoc_number=rp.frdoc_number
                      JOIN frdocs fr ON r.frdoc_number=fr.frdoc_number
                      GROUP BY r.frdoc_number, r.response_id""")

    cursor.execute("""CREATE INDEX IF NOT EXISTS frdoc_and_response_index ON cache_responses_page (frdoc_number, response_id)""")

    connection.commit()

    print("Created responses table")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_org_agency AS
                    SELECT org_name, agency, SUM(changedDoc) AS docs_changed, number_of_docs, (CAST(SUM(changedDoc) AS REAL)/number_of_docs) as influence_percentage FROM
                    (SELECT org_name, agency, SUM(score>0.5) > 0 AS changedDoc
                    FROM comment_orgs
                    INNER JOIN comment_responses ON comment_orgs.comment_id=comment_responses.comment_id
                    INNER JOIN frdoc_agencies ON comment_responses.frdoc_number=frdoc_agencies.frdoc_number
                    GROUP BY agency, frdoc_agencies.frdoc_number, org_name)
                    LEFT JOIN (SELECT frdoc_agencies.agency as count_agency, COUNT(*) AS number_of_docs FROM frdoc_agencies GROUP BY frdoc_agencies.agency) ON count_agency=agency
                    GROUP BY agency, org_name;""")

    connection.commit()

    print("Created cache_org_agency")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_org_doc_changes AS
                    SELECT org_name, frdoc_number, SUM(CASE WHEN score>0.5 THEN 1 ELSE 0 END) AS sumScore
                    FROM org_responses
                    GROUP BY org_name, frdoc_number;""")

    connection.commit()

    print("Created cache_org_doc_changes")

    connection.commit()
    cursor.close()
    connection.close()

except Exception as e:
    print(e)



