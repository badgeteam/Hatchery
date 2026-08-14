<!--
SPDX-FileCopyrightText: 2017 - 2026 Badge.Team contributors
SPDX-License-Identifier: MIT
-->

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
/weather                      - weather of the configured location, proxied from open-meteo.com
/weather/52.3451,5.4581       - weather of specified geolocation proxied
```

The forecast came from darksky.net until Apple switched that API off on
31 March 2023. It now comes from [Open-Meteo](https://open-meteo.com/), which
needs no API key, but the response is translated back into the shape Dark Sky
returned: badges in the field cannot be updated, so the field names, the icon
names and the `units=ca` units (Celsius, km/h, kilometres, hPa, mm) are all
unchanged. Forecast data by Open-Meteo.com, licensed CC BY 4.0.

### API Playground

```uri
/app
```

Live version <https://hatchery.badge.team/api>
