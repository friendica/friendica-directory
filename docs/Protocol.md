# Friendica Directory Protocol

## Search

```
GET /search[/account_type]?q=...
Accept: application/json
```

Parameters:
- `account_type`: An arbitrary account type string. Currently supported are `all`, `people` and `forum`. Default is `all`.
- `q`: The search query.

Returns a JSON structure containing a paginated list profiles matching the search query and the optional account type.

Example:

```json
{
      "query": "philosophy",
      "page": 1,
      "itemsperpage": 20,
      "count": "13",
      "profiles": [
            {
                  "id": "2259",
                  "name": "Hyp🌧lite Pe☂ov🍃n (he/him)",
                  "username": "hypolite",
                  "addr": "hypolite@friendica.mrpetovan.com",
                  "account_type": "People",
                  "pdesc": "Subpar geek, french/english, science, games, feminism, jokes and anything in between. Avatar by @DearMsDear@mastodon.art",
                  "locality": "Brooklyn",
                  "region": "New York",
                  "country": "USA",
                  "profile_url": "https://friendica.mrpetovan.com/profile/hypolite",
                  "dfrn_request": "https://friendica.mrpetovan.com/dfrn_request/hypolite",
                  "photo": "https://friendica.mrpetovan.com/photo/27330388315ae4ed2b03e3c116980490-4.jpg?ts=1541567135",
                  "tags": "videogame gaming boardgame politics philosophy development programming php",
                  "last_activity": "2018-45"
            },
            ...
      ]
}    
```


## Profile submission

`GET /submit?url=...`

Parameters:
- `url`: a hexadecimal-encoded profile URL to be added to the directory.

The success of the operation is indicated by the HTTP response code.

## Synchronization

`GET /sync/pull/all`

Returns a JSON structure containing all the profiles displayed by the directory.

Example:
```json
{
    now: 1541942034,
    count: 7435,
    results: [
        "http://example.com/profile/test",
        ...
    ]
}
```

`GET /sync/pull/since/1541942034`

Returns a JSON structure containing profiles displayed by the directory that have been updated since the provided UNIX timestamp.

Example:
```json
{
    now: 1541942160,
    count: 2766,
    results: [
        "http://example.com/profile/test",
        ...
    ]
}
```