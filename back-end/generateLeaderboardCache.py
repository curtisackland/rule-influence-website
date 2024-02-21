import sqlite3
import pandas as pd

def cache_leaderboard_df(db_path):
    db = sqlite3.connect(db_path)
    
    leaderboard_df = pd.read_sql_query('''
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
                    
                    ''', db)
    
    org_rule_counts_df = pd.read_sql_query('''
                    SELECT DISTINCT
                    org_name,
                    COUNT(DISTINCT(frdoc_input_comments.frdoc_number)) as org_rules

                    FROM frdoc_input_comments
                    INNER JOIN comment_orgs
                    ON (comment_orgs.comment_id==frdoc_input_comments.comment_id)
                                       
                    INNER JOIN response_sample_frdocs
                    ON (response_sample_frdocs.frdoc_number==frdoc_input_comments.frdoc_number)
                                               
                    GROUP BY org_name
                    ''', db)
    
    full_leaderboard_df = org_rule_counts_df.merge(leaderboard_df, on='org_name', how='left')
    full_leaderboard_df = full_leaderboard_df.fillna(0)
    
    full_leaderboard_df['org_responses'] = full_leaderboard_df['org_responses'].astype(int)
    full_leaderboard_df['org_changes'] = full_leaderboard_df['org_changes'].astype(int)
    
    # Sort 
    full_leaderboard_df = full_leaderboard_df.sort_values(['org_changes', 'org_responses', 'org_rules'], ascending=False)
    full_leaderboard_df.to_sql('cache_leaderboard', db, if_exists='replace', index=False)

cache_leaderboard_df('data/rulemaking_influence.db')
