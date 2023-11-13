<?php

namespace App\Utils\Search;

class SearchResult
{
    public readonly array $results;
    public readonly int $current_page;
    public readonly int $total_pages;

    public function __construct(array $results, int $current_page, int $total_pages)
    {
        $this->results = $results;
        $this->current_page = $current_page;
        $this->total_pages = $total_pages;
    }
}