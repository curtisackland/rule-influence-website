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

    connection.commit()
    cursor.close()
    connection.close()

except Exception as e:
    print(e)



