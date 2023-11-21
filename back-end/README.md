# Rule Influencing Website Back-end

Local back-end url: ```localhost:8080```

## Routes

These are what the routes look like and what the backend expects the query parameters to be.
An example query parameter payload can be found in ```front-end/src/components/TopOrgCommentersTable.vue```
in the ```fetchData()``` method.

**Home Page &rarr; ```/``` OR ```/api/home```**
```
{
    filters: {
        orgName: "String" || NULL,
        sortBy: "orgName" || "yCount" || "frdocs" || NULL,
        sortOrder: "DESC" || "ASC" || NULL,
    }
}
```
Note: If ```sortBy``` or ```sortOrder``` are ```NULL``` then no sort will be applied.

**Organization Page &rarr; ```/api/organization/{orgName}```**
```
{
    filters: {
        commentID: "String" || NULL, // can be a string of the comment id
        frdocNumber: "String" || NULL, // can be a string of the frdoc number
        sortBy: "responseID" || "score" || "normScore" || NULL,
        sortOrder: "DESC" || "ASC" || NULL
    }
}
```
Note: If ```sortBy``` or ```sortOrder``` are ```NULL``` then no sort will be applied.