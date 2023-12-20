import sqlite3

db_file = 'data/rulemaking_influence.db'

try:
    connection = sqlite3.connect(db_file)

    cursor = connection.cursor()

    cursor.execute("DROP TABLE IF EXISTS cache_home_page")

    cursor.execute("DROP TABLE IF EXISTS cache_org_page")

    cursor.execute("""CREATE TABLE cache_home_page AS
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

    cursor.execute("""CREATE TABLE cache_org_page AS
                    SELECT
                    comment_responses.comment_id,
                    comment_orgs.org_name,
                    comment_responses.frdoc_number,
                    comment_responses.response_id,
                    comment_responses.score,
                    comment_responses.norm_score
                    FROM comment_responses, comment_orgs
                    WHERE comment_orgs.comment_id==comment_responses.comment_id;""")

    print("Created org page table")

    cursor.execute("""CREATE TABLE cache_comment_page AS
                      SELECT fc.frdoc_number, fc.comment_id, fc.count,
                             COALESCE(COUNT(cr.response_id), 0) AS linked_responses,
                             JSON_GROUP_ARRAY(DISTINCT org_name) AS orgs, JSON_GROUP_ARRAY(DISTINCT agency) AS agencies
                      FROM frdoc_comments fc
                               LEFT JOIN comment_responses cr ON fc.comment_id = cr.comment_id
                               LEFT JOIN comment_orgs co ON fc.comment_id = co.comment_id
                               LEFT JOIN frdoc_agencies fa ON fc.frdoc_number = fa.frdoc_number
                      GROUP BY fc.comment_id, fc.frdoc_number, fc.count
                      ORDER BY fc.count DESC;""")

    print("Created comments table")

    connection.commit()
    cursor.close()
    connection.close()

except Exception as e:
    print(e)



