<?php

/**
 * rankreport.php — Application-specific configuration
 */

return [

    // ─── CSV Import ───────────────────────────────────────────────────────────

    'max_csv_size_mb'    => 20,
    'preview_rows_limit' => 20,

    /**
     * Expected Ahrefs CSV column headers.
     * Internal key => Expected CSV header string
     * The CsvParserService will fuzzy-match these.
     */
    'csv_ahrefs_columns' => [
        'keyword'          => 'Keyword',
        'current_position' => 'Current position',
        'volume'           => 'Volume',
        'current_url'      => 'Current URL',
        'country_code'     => 'Country',
        'location'         => 'Location',
        'branded'          => 'Branded',
        'entities'         => 'Entities',
        'kd'               => 'KD',
        'cpc'              => 'CPC',
        'organic_traffic'  => 'Organic traffic',
        'serp_features'    => 'SERP features',
        'informational'    => 'Informational',
        'commercial'       => 'Commercial',
        'navigational'     => 'Navigational',
        'transactional'    => 'Transactional',
    ],

    // ─── Visibility Score ─────────────────────────────────────────────────────

    /**
     * Point values per exact position.
     * Range-based fallback is handled in VisibilityScoreService.
     */
    'visibility_points' => [
        1 => 100,
        2 => 90,
        3 => 80,
        // 4-5 → 70 (handled in service)
        // 6-10 → 50
        // 11-20 → 20
        // 21-50 → 5
        // 51-100 → 1
        // >100 → 0
    ],

    // ─── Position Groups ─────────────────────────────────────────────────────

    'position_groups' => [
        'top_3'   => ['min' => 1,   'max' => 3,   'label' => 'Top 3',    'color' => '#27ae60'],
        'top_10'  => ['min' => 4,   'max' => 10,  'label' => 'Top 10',   'color' => '#2980b9'],
        'top_20'  => ['min' => 11,  'max' => 20,  'label' => 'Top 20',   'color' => '#8e44ad'],
        'top_50'  => ['min' => 21,  'max' => 50,  'label' => 'Top 50',   'color' => '#f39c12'],
        'top_100' => ['min' => 51,  'max' => 100, 'label' => 'Top 100',  'color' => '#d35400'],
        'outside' => ['min' => 101, 'max' => 9999,'label' => 'Outside',  'color' => '#c0392b'],
    ],

    // ─── Storage Paths ────────────────────────────────────────────────────────

    'storage' => [
        'csv_path' => 'imports/csv',
        'pdf_path' => 'reports/pdf',
    ],

    // ─── Chart Colors ─────────────────────────────────────────────────────────

    'chart_colors' => [
        'main'       => '#3c8dbc',
        'competitor' => ['#f39c12', '#00a65a', '#dd4b39', '#605ca8', '#d81b60', '#39cccc'],
        'positive'   => '#27ae60',
        'negative'   => '#e74c3c',
        'neutral'    => '#95a5a6',
    ],

];
