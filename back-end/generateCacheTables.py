import sqlite3

db_file = 'data/rulemaking_influence.db'
tableNames = [
"cache_home_page",
"cache_org_page",
"cache_frdocs_page",
"cache_comment_page",
]

tablesToDrop = tableNames
#tablesToDrop = ["cache_frdocs_page"]
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

    cursor.execute("""CREATE TABLE IF NOT EXISTS cache_comment_page AS
                      SELECT fc.frdoc_number, fc.comment_id, fr.title,
                             COALESCE(COUNT(cr.response_id), 0) AS linked_responses,
                             SUM(COALESCE(cr.score, 0) > 0.5) AS number_of_changes,
                             JSON_GROUP_ARRAY(DISTINCT org_name) AS orgs, JSON_GROUP_ARRAY(DISTINCT agency) AS agencies
                      FROM frdoc_comments fc
                               LEFT JOIN comment_responses cr ON fc.comment_id = cr.comment_id
                               LEFT JOIN comment_orgs co ON fc.comment_id = co.comment_id
                               LEFT JOIN frdoc_agencies fa ON fc.frdoc_number = fa.frdoc_number
                               LEFT JOIN frdocs fr ON fc.frdoc_number = fr.frdoc_number
                      GROUP BY fc.frdoc_number, fc.comment_id""")

    cursor.execute("""CREATE INDEX linked_responses ON cache_comment_page (linked_responses)""")
    cursor.execute("""CREATE INDEX number_of_changes ON cache_comment_page (number_of_changes)""")

    print("Created comments table")


    connection.commit()
    cursor.close()
    connection.close()

except Exception as e:
    print(e)



