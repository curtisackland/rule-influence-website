import os
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
"cache_leaderboard"
]

tablesToDrop = tableNames
#tablesToDrop = ["cache_comment_page"]
try:

    if not os.path.exists(db_file):
        raise Exception("Database file missing. Please place it in the rule-influence-website/back-end/data directory.")

    connection = sqlite3.connect(db_file)

    cursor = connection.cursor()
    for tableName in tablesToDrop:
        cursor.execute(f"DROP TABLE IF EXISTS {tableName}")
        print(f"Dropped cached table {tableName}")

    cursor.execute("""CREATE INDEX IF NOT EXISTS comment_id_index_frdoc_comments ON frdoc_comments (comment_id)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS comment_id_index ON comment_responses (comment_id)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS frdoc_response_id_index_comment_responses ON comment_responses (frdoc_number, response_id)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS comment_id_index_frdoc_comments ON frdoc_comments (comment_id)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS comment_id_index_frdoc_input_comments ON frdoc_input_comments (comment_id)""")

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

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_leaderboard AS
                        SELECT org_rule_counts_df.org_name,
                             coalesce(org_rules, 0) AS org_rules,
                             coalesce(org_responses, 0) AS org_responses,
                             coalesce(org_changes, 0) AS org_changes
                      FROM (SELECT DISTINCT
                                  org_name,
                                  COUNT(DISTINCT(frdoc_input_comments.frdoc_number)) as org_rules
                              FROM frdoc_input_comments
                              INNER JOIN comment_orgs
                                  ON (comment_orgs.comment_id==frdoc_input_comments.comment_id)
                              INNER JOIN response_sample_frdocs
                                  ON (response_sample_frdocs.frdoc_number==frdoc_input_comments.frdoc_number)
                            GROUP BY org_name
                      ) org_rule_counts_df
                      LEFT JOIN (
                          SELECT DISTINCT
                              org_name,
                              COUNT(y_prob) as org_responses,
                              SUM(y_prob>0.5) AS org_changes
                          FROM org_responses
                          INNER JOIN responses
                              ON (responses.frdoc_number==org_responses.frdoc_number)
                              AND (responses.response_id==org_responses.response_id)
                          GROUP BY org_name
                          ORDER BY -org_changes
                      ) leaderboard_df
                      ON org_rule_counts_df.org_name = leaderboard_df.org_name
                      ORDER BY org_changes DESC, org_responses DESC, org_rules DESC;""")

    connection.commit()

    print("Created cache leaderboard table")

    # This query depends on cache_leaderboard
    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_org_page AS
                    SELECT A.org_name, number_of_comments, y_prob_avg, org_rules AS total_rules, A.org_responses AS total_response_count, A.org_changes AS total_rules_changed
                    FROM cache_leaderboard A
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
                    ) ON A.org_name=count_org_name;""")

    connection.commit()

    print("Created org page table")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_frdocs_page AS
                  SELECT
                      frdocs.frdoc_number,
                      publication_date,
                      fr_type,
                      type,
                      json_group_array(DISTINCT(agency)) as agencies,
                      title,
                      abstract,
                      action,
                      JSON_GROUP_ARRAY(DISTINCT prevSeq.frdoc_i) as prevFRDoc,
                      JSON_GROUP_ARRAY(DISTINCT nextSeq.frdoc_j) as nextFRDoc,
                      COALESCE(responseCount, 0) as response_count,
                      COALESCE(commentCount, 0) as comment_count,
                      COALESCE(change_count, 0) as change_count,
                      orgs
                  FROM frdocs
                      LEFT JOIN frdoc_agencies ON frdocs.frdoc_number=frdoc_agencies.frdoc_number
                      LEFT JOIN frdoc_sequences prevSeq ON frdocs.frdoc_number = prevSeq.frdoc_j
                      LEFT JOIN frdoc_sequences nextSeq ON frdocs.frdoc_number = nextSeq.frdoc_i
                      LEFT JOIN (SELECT frdoc_number, COUNT(*) as responseCount, SUM(CASE WHEN y_prob>0.5 THEN 1 ELSE 0 END) as change_count FROM responses GROUP BY frdoc_number) responses ON frdocs.frdoc_number = responses.frdoc_number
                      LEFT JOIN (SELECT frdoc_number, COUNT(DISTINCT comment_id) as commentCount FROM comment_responses GROUP BY frdoc_number) comments ON frdocs.frdoc_number = comments.frdoc_number
                      LEFT JOIN (SELECT frdoc_number, JSON_GROUP_ARRAY(DISTINCT org_name) as orgs FROM comment_responses LEFT JOIN comment_orgs ON comment_responses.comment_id=comment_orgs.comment_id GROUP BY frdoc_number) comments_orgs ON frdocs.frdoc_number = comments_orgs.frdoc_number
                  GROUP BY frdocs.frdoc_number;""")

    cursor.execute("""CREATE INDEX IF NOT EXISTS frdoc_number_index ON cache_frdocs_page (frdoc_number)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS frdoc_title_index ON cache_frdocs_page (title)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS date_index ON cache_frdocs_page (publication_date)""")

    connection.commit()

    print("Created cache_frdocs_page")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_comment_page AS
                      SELECT fc.comment_id, fc.receive_date, number_of_frdocs,
                             COALESCE(COUNT(re.any_change), 0)                               AS linked_responses,
                             SUM(COALESCE(CASE WHEN re.any_change='Y' THEN 1 ELSE 0 END, 0)) AS number_of_changes,
                             co.orgs
                      FROM
                      (SELECT comment_id, receive_date,
                             COUNT(DISTINCT frdoc_number) AS number_of_frdocs
                      FROM (SELECT comment_id, frdoc_number, receive_date FROM frdoc_comments UNION SELECT frdoc_number, comment_id, receive_date FROM frdoc_input_comments)
                      GROUP BY comment_id) fc
                          LEFT JOIN comment_responses cr ON fc.comment_id=cr.comment_id
                          LEFT JOIN responses re ON cr.frdoc_number=re.frdoc_number AND cr.response_id=re.response_id
                          LEFT JOIN (SELECT comment_id, JSON_GROUP_ARRAY(DISTINCT org_name) as orgs FROM comment_orgs GROUP BY comment_id) co ON fc.comment_id = co.comment_id
                      GROUP BY fc.comment_id""")

    cursor.execute("""CREATE INDEX IF NOT EXISTS linked_responses ON cache_comment_page (linked_responses)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS number_of_changes ON cache_comment_page (number_of_changes)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS number_of_frdocs_index_cp ON cache_comment_page (number_of_frdocs)""")
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
                             rp.text
                      FROM responses r
                               LEFT JOIN comment_responses cr ON r.response_id=cr.response_id AND r.frdoc_number=cr.frdoc_number
                               LEFT JOIN (SELECT frdoc_number, response_id, JSON_GROUP_ARRAY(text) as text FROM response_paragraphs GROUP BY frdoc_number, response_id ORDER BY response_id DESC, text_id ASC) rp on r.response_id=rp.response_id AND r.frdoc_number=rp.frdoc_number
                               LEFT JOIN frdocs fr ON r.frdoc_number=fr.frdoc_number
                      GROUP BY r.frdoc_number, r.response_id""")

    cursor.execute("""CREATE INDEX IF NOT EXISTS frdoc_and_response_index ON cache_responses_page (frdoc_number, response_id)""")
    cursor.execute("""CREATE INDEX IF NOT EXISTS responses_number_linked_comments_index ON cache_responses_page (number_of_comments)""")

    connection.commit()

    print("Created responses table")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_org_agency AS
                    SELECT
                        AGENCY_RCR.org_name,
                        AGENCY_RCR.agency,
                        agency_rules,
                        agency_responses,
                        agency_changes,
                        change_ratio,
                        agency_comments

                    FROM (
                        SELECT
                            org_name,
                            agency,
                            COUNT(y_prob) AS agency_responses,
                            SUM(y_prob>0.5) AS agency_changes,
                            (CAST(SUM(y_prob>0.5) AS REAL)) / (CAST(COUNT(y_prob) AS REAL)) AS change_ratio
                        FROM org_responses
                        INNER JOIN responses ON responses.frdoc_number=org_responses.frdoc_number
                            AND responses.response_id=org_responses.response_id
                        INNER JOIN frdoc_agencies
                                   ON responses.frdoc_number=frdoc_agencies.frdoc_number
                        GROUP BY org_name, agency) AGENCY_RCR
                    INNER JOIN (
                        SELECT org_name, frdoc_agencies.agency, COUNT(DISTINCT(frdoc_agencies.frdoc_number)) AS agency_rules FROM org_responses
                        INNER JOIN responses
                            ON responses.frdoc_number=org_responses.frdoc_number
                            AND responses.response_id=org_responses.response_id
                        INNER JOIN frdoc_agencies
                            ON responses.frdoc_number=frdoc_agencies.frdoc_number
                        GROUP BY org_name, agency
                    ) AGENCY_COUNT ON AGENCY_RCR.org_name=AGENCY_COUNT.org_name AND AGENCY_RCR.agency=AGENCY_COUNT.agency

                    INNER JOIN (
                        SELECT org_name, agency, COUNT(DISTINCT A.comment_id) AS agency_comments FROM
                            (SELECT
                                frdoc_number,
                                comment_id,
                                comment_id AS agency_comments
                            FROM frdoc_comments
                            UNION
                            SELECT frdoc_number,
                                   comment_id,
                                comment_id AS agency_comments
                            FROM frdoc_input_comments
                        ) A
                        INNER JOIN comment_orgs ON A.comment_id = comment_orgs.comment_id
                        INNER JOIN frdoc_agencies ON A.frdoc_number=frdoc_agencies.frdoc_number
                        GROUP BY org_name, agency
                    ) Comments ON Comments.org_name=AGENCY_RCR.org_name AND Comments.agency=AGENCY_RCR.agency
                    GROUP BY AGENCY_RCR.org_name, AGENCY_RCR.agency;""")

    connection.commit()

    print("Created cache_org_agency")

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_org_doc_changes AS
                    SELECT org_name, responses.frdoc_number, SUM(y_prob > 0.5) AS sumScore FROM comment_orgs
                    INNER JOIN comment_responses ON comment_orgs.comment_id = comment_responses.comment_id
                    INNER JOIN responses ON comment_responses.frdoc_number = responses.frdoc_number AND  comment_responses.response_id = responses.response_id
                    GROUP BY org_name, responses.frdoc_number;""")

    connection.commit()

    print("Created cache_org_doc_changes")

    connection.commit()
    cursor.close()
    connection.close()

except Exception as e:
    print(e)



