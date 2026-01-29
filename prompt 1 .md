so i want to migrate a website tech stack php to laravel+ inertia + vue tailwind css so i have little qurery
about the backend code because it is complex so in my php website  a make a system that create/delete post  and user have ability to first create circle and add some user in circle and share a post in any circle and only circle user see this post(mean i implement the idea of google plus circle ) but now in my laravel backend my current development progress is on user create post and like/dislike and add comment on that post and all users post is currently display on home page but  accroding to my agend of previous website user see only those post which is creted by it or if any user share post in it own created circle and it will be the part of that circle 
i give you both website databse export sql
so tell me what kink of table now i made in laravel webstite 
and complete workflow of that system working in laravel vue website


I am migrating an old PHP-based social website to a modern stack using Laravel + Inertia + Vue + Tailwind CSS.

Existing (Old PHP) System

In my old PHP application, I implemented a Google Plus–style Circles system with the following features:

Users can:

Create posts

Create circles

Add users to circles

Share posts into one or more circles

A user can see:

Their own posts

Posts shared into circles where:

They are the circle owner, or

They are added as a member

Posts outside a user’s circles are not visible

Database tables included:

circle

circlefriend

note

sharenoteincircle

comment

I used complex SQL queries to fetch:

User’s own posts

Posts shared into joined circles

Associated comments and authors

(The full PHP database schema and example queries are provided above.)

Current (Laravel) Progress

In my Laravel application, I have already implemented:

User authentication

Posts (create, delete)

Likes / dislikes

Comments

Currently, all users see all posts on the home page, which is incorrect.

Current Laravel tables include:

users

posts

likes

comments

(The full Laravel database schema is provided above.)

What I Need Help With

I want to re-implement the Circle-based post visibility system in Laravel.

Please explain:

Which new tables I should create in Laravel

Circles

Circle members

Post–circle sharing

Recommended Laravel table structure (relations, foreign keys)

Eloquent model relationships

User ↔ Circles

Circles ↔ Members

Posts ↔ Circles

Complete backend workflow

Creating a circle

Adding/removing users from a circle

Sharing a post into a circle

Fetching posts visible to a logged-in user

How the home feed query should work

Show only:

User’s own posts

Posts shared into circles the user belongs to

How this logic fits with Laravel + Inertia + Vue

Controller logic

Data passed to Vue components

The goal is to fully replicate my old PHP circle-based visibility logic using Laravel best practices.