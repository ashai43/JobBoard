Job Board

A small job board: people browse job ads, open one for the full details, and apply in a couple of clicks. Recruiters get their own space to post ads and follow up on applications, and there's an admin page behind everything.

We built it with:





MariaDB for the database



Symfony 7.4 + API Platform 4 for the REST API (JWT auth)



React 19 + Vite for the front end

jobboard/
├── backend/            Symfony API
├── frontend/           React app
├── docs/               SQL schema if you want to look at it directly
└── docker-compose.yml  spins up MariaDB, if you don't have it installed

