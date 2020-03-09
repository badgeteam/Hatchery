# badge.Team Hatchery

A platform to publish and develop software for several electronic badges.

## API

Apps

```uri
/eggs/get/[app]/json          - get json data for a the egg named [app]
/eggs/list/json               - a list of all eggs with description, revision etc
/eggs/search/[words]/json     - json data for search query [words]
/eggs/categories/json         - json list of categories
/eggs/category/[cat]/json     - json data for category [cat]

/basket/[badge]/list/json           - a list of all eggs for specific [badge]
/basket/[badge]/search/[words]/json - [badge] specific search for [words]
/basket/[badge]/categories/json     - [badge] specific list of categories
/basket/[badge]/category/[cat]/json - json data for category [cat] on [badge]
```

App specific

```uri
/weather                      - weather of sha location proxied from darksky.net
/weather/52.3451,5.4581       - weather of specified geolocation proxied
```

### API Playground

```uri
/app
```

Live version <https://badge.team/api>