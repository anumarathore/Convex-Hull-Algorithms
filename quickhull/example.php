<?php
include( 'convex_hull.php' );

$points = array( 
    array( 1, 1 ),
    array( 1, 2 ),
    array( 2, 1 ),
    array( 3, 3 )
);

$hull = new ConvexHull( $points );

var_dump( $hull->getHullPoints() );
