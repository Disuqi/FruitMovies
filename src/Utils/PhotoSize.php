<?php

namespace App\Utils;



enum PhotoSize : string
{
    private const BASE_IMAGE_URL = 'https://image.tmdb.org/t/p/';
    case Large = PhotoSize::BASE_IMAGE_URL . 'w1280';
    case Medium = PhotoSize::BASE_IMAGE_URL . 'w500';
    case Small = PhotoSize::BASE_IMAGE_URL . 'w200';
}