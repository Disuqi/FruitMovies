<?php

namespace App\Utils\Search;

enum OrderMoviesBy : string
{
    case None = 'none';
    case Title = 'title';
    case Rating = 'rating';
    case ReleaseDate = 'release_date';
    case Reviews = 'reviews';
}