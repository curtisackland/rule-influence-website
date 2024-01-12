import sqlite3

db_file = 'data/rulemaking_influence.db'
tableNames = [
"cache_home_page",
"cache_frdocs_page",
"cache_comment_page",
<<<<<<< Updated upstream
"cache_responses_page",
=======
"cache_org_page",
"cache_org_agency",
"cache_org_doc_changes",
>>>>>>> Stashed changes
]

tablesToDrop = tableNames
#tablesToDrop = ["cache_comment_page"]
try:
    connection = sqlite3.connect(db_file)

    cursor = connection.cursor()
    for tableName in tablesToDrop:
        cursor.execute(f"DROP TABLE IF EXISTS {tableName}")
        print(f"Dropped cached table {tableName}")

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

    print("Created home page table")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_org_page AS
                    SELECT comment_orgs.org_name, comment_id, y_prob_avg, coalesce(response_count, 0) AS total_response_count, coalesce(rules_changed, 0) AS total_rules_changed
                    FROM comment_orgs LEFT JOIN (
                        SELECT comment_id AS inner_comment_id, AVG(y_prob) AS y_prob_avg, COUNT(responses.response_id) AS response_count, SUM(CASE WHEN y_prob > 0.5 THEN 1 ELSE 0 END) AS rules_changed FROM comment_responses
                        LEFT JOIN responses ON comment_responses.frdoc_number=responses.frdoc_number AND comment_responses.response_id=responses.response_id
                        GROUP BY comment_id) ON comment_orgs.comment_id=inner_comment_id
                        LEFT JOIN (SELECT org_name AS count_org_name, COUNT(DISTINCT(comment_id)) FROM comment_orgs GROUP BY org_name) ON comment_orgs.org_name=count_org_name
                    ORDER BY org_name;""")

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
                         prevSeq.frdoc_i as prevFRDoc,
                         nextSeq.frdoc_j as nextFRDoc,
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

    print("Created cache_frdocs_page")

    # TODO look into the disparity between frdoc_comments and comment_responses frdoc_numbers
    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_comment_page AS
                      SELECT cr.frdoc_number, fc.comment_id, fr.title,
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

    print("Created comments table")

    cursor.execute("""CREATE TABLE IF NOT EXISTS temp_table AS
                      SELECT cr.frdoc_number, cr.response_id, cr.comment_id, cr.score > 0.5 AS outcome, tr.number_of_comments
                      FROM (SELECT cr.frdoc_number, cr.response_id, COUNT(cr.comment_id) AS number_of_comments
                            FROM comment_responses AS cr
                            WHERE (cr.frdoc_number, cr.response_id) IN (
                                SELECT frdoc_number, response_id FROM comment_responses GROUP BY frdoc_number, response_id
                            )
                            GROUP BY cr.frdoc_number, cr.response_id) tr
                               JOIN comment_responses cr ON tr.frdoc_number=cr.frdoc_number AND tr.response_id=cr.response_id""")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_responses_page AS
                        SELECT tr.*, fr.title
                        FROM temp_table tr
                        JOIN frdocs fr ON fr.frdoc_number=tr.frdoc_number""")

    cursor.execute("""CREATE INDEX IF NOT EXISTS frdoc_and_response_index ON cache_responses_page (frdoc_number, response_id)""")

    cursor.execute("""DROP TABLE temp_table""")

    print("Created responses table")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_org_agency AS
                    SELECT org_name, agency, SUM(changedDoc) AS docs_changed, number_of_docs FROM
                          (SELECT org_name, agency, SUM(score>0.5) > 0 AS changedDoc
                          FROM comment_orgs
                          INNER JOIN comment_responses ON comment_orgs.comment_id=comment_responses.comment_id
                          INNER JOIN frdoc_agencies ON comment_responses.frdoc_number=frdoc_agencies.frdoc_number
                          GROUP BY agency, frdoc_agencies.frdoc_number, org_name)
                          LEFT JOIN (SELECT frdoc_agencies.agency as count_agency, COUNT(*) AS number_of_docs FROM frdoc_agencies GROUP BY frdoc_agencies.agency) ON count_agency=agency
                    GROUP BY agency, org_name;""")

    print("Created cache_org_agency")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_org_doc_changes AS
                    SELECT org_name, frdoc_number, SUM(CASE WHEN score>0.5 THEN 1 ELSE 0 END) AS sumScore
                    FROM org_responses
                    GROUP BY org_name, frdoc_number;""")

    print("Created cache_org_doc_changes")


    connection.commit()
    cursor.close()
    connection.close()

except Exception as e:
    print(e)



