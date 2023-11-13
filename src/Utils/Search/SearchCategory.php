<?php

namespace App\Utils\Search;

enum SearchCategory : string
{
    case Popular = "popular";
    case TopRated = "top rated";
    case Latest = "latest";
    case Upcoming = "upcoming";
    case All = "all";
}