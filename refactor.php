<?php

function keepMethods($file, $methodsToKeep) {
    $content = file_get_contents($file);
    
    // We will use naive regex but very carefully.
    // It's better to use regex to find method positions and slice.
    $tokens = token_get_all($content);
    $methods = [];
    $currentMethod = null;
    $braceCount = 0;
    $methodStart = 0;
    $inMethod = false;

    // VERY NAIVE parsing. Let's just use string manipulations.
}

$sportsMethodsToKeep = [
    'listLeagues', 'listLeaguesMain', 'getModalities', 'getLiveMarketConfig'
];

$matchMethodsToKeep = [
    'getMatchesByDay', 'getMatchesLive', 'getDaysList', 'getMatchesByModality',
    'getMatchesHome', 'getMatchesAmanha', 'getMatchesDepoisAmanha', 'getMatchesByDate',
    'getOdds', 'getMatchesSearch', 'getMatches', 'getFeaturedMatches', 'searchLeague', 'searchTeam'
];

// Instead of complex parsing in PHP, I'll just leave the controllers as they are!
// Wait! If they extend ApiController and don't delete the inherited methods, there's NO code duplication at runtime, but the files are huge!
// Let's use PowerShell regex replacement to drop methods.
