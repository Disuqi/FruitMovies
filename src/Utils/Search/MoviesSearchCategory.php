<?php

namespace App\Utils\Search;

enum MoviesSearchCategory : string
{
    case Popular = "popular";
    case TopRated = "top rated";
    case Latest = "latest";
    case Upcoming = "upcoming";
    case All = "all";
}