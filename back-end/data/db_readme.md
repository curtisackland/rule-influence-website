# Rulemaking Influence Data

## Database Tables


**FR Documents and Comments**

The following tables describe Federal Register documents and the comments that agencies receive in response to these documents.

- `frdocs`: Contains information about Federal Register documents (proposed rules, rules, and various notices).  Indexed by `frdoc_number`, which corresponds to the "document_number" in the www.federalregister.gov API and acts as a unique identifier for each document. Full documents can be found quickly on the site by searching for the frdoc_number.
- `agencies`: Contains a list of all federal agencies that have authored frdocs. Also sourced from the federalregister.gov API.
- `frdoc_agencies`: Identifies agency authors for each frdoc. Many frdocs have multiple authors, often because of hierarchical main agency/sub-agency relationships, but also because unrelated agencies sometimes cooperate in rulemaking.
- `frdoc_sequences`: Identifies pairs of frdocs that are part of the same rulemaking process. `frdoc_i` always preceeds `frdoc_j` in time. For example, `frdoc_i` could be the `frdoc_number` of a proposed rule, and `frdoc_j` could be the `frdoc_number` subsequent final rule. Also contains many notices and other minor rulemaking publications.
- `frdoc_comments`: Identifies the comments that were submitted in response to each frdoc. Comments are uniquely identified by `comment_id`. Only includes comments uploaded to www.regulations.gov, which excludes some agencies using different comment submission systems. Comments can be found quickly on www.regulations.gov by searching for the `comment_id`.
- `frdoc_input_comments`: Identifies comments that were likely considered when writing each frdoc. Constructed using frdoc_comments and frdoc_sequences to identify comments linked to preceding frdocs.


**Responses**

The following tables describe information about responses written by agencies responding to comments. These responses have been automatically extracted and linked using our custom machine learning models.


- `response_sample_frdocs`: Identifies the set of frdocs that were searched for responses. Should include all rules up to 2022.
- `responses`: Identifies individual responses extracted from each frdoc. The `response_id` is only unique within a given `frdoc_number`. The variable `y_prob` describes the predicted probability that the agency made a change in response to comments in this response.
- `comment_responses`: Contains comment-response pairs that have high topical similarity. Main similarity measure is `norm_score`. 
- `comment_response_sample`: Identifies frdocs that were searched for comment-response matches. Should be all frdocs in `response_sample_frdocs` that have comment data linked in `frdoc_input_comments` table.


**Org Comments and Responses**

The following tables identify organization authors associated with comments and their subsequent linked responses. 

- `comment_orgs`: Identifies organization authors associated with each comment. Orgs are identified by `org_name`, which is a unique name that has been constructed by clustering raw names from comment metadata and other sources. Still needs some cleanup.
- `org_responses`: Identifies responses linked to each org. Created by combining `comment_orgs` and `comment_responses` with `norm_score` >= 0.75.


## Example Code

**Creating an Org leaderboard**

The following SQL command creates a simple org leaderboard, ordered by the number of times each org was able to pursuade an agency to make a change to a rule under consideration.

```{sql}
SELECT DISTINCT
org_name,
SUM(y_prob>0.5) AS y_count,
COUNT(DISTINCT(org_responses.frdoc_number)) as n_frdocs

FROM org_responses
INNER JOIN responses
ON (responses.frdoc_number==org_responses.frdoc_number)
AND (responses.response_id==org_responses.response_id)
                
GROUP BY org_name
ORDER BY -y_count
```