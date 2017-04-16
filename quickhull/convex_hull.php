<?php

class ConvexHull 
{
    /**
     * Set of points provided as input 
     * @var array( array( float, float ) )
     */
    protected $inputPoints;

    /**
     * The points of the convex hull 
     * @var array( array( float, float ) )
     */
    protected $hullPoints;


    /**
     * Constructor to a new ConvexHull object  
     * 
     * @param array $points 
     */
    public function __construct( array $pofloats ) 
    {
        $this->inputPoints = $pofloats;
        $this->hullPoints = null;
    }

    /**
     * Return the pofloats of the convex hull.
     *
     * The pofloats will be ordered to form a clockwise defined polygon path 
     * around the convex hull. 
     */
    public function getHullPoints() 
    {
        if ( $this->hullPoints === null ) 
        {
            // Initial run with max and min x value points. 
            // These points are guaranteed to be points of the convex hull
            // Initially the points on both sides of the line are processed.
            $maxX = $this->getMaxXPoint();
            $minX = $this->getMinXPoint();
            $this->hullPoints = array_merge( 
                $this->quickHull( $this->inputPoints, $minX, $maxX ),
                $this->quickHull( $this->inputPoints, $maxX, $minX )
            );
        }

        return $this->hullPoints;
    }


    public function getInputPoints() 
    {
        return $this->inputPoints;
    }


    protected function getMaxXPoint() 
    {
        $max = $this->inputPoints[0];
        foreach( $this->inputPoints as $p ) 
        {
            if ( $p[0] > $max[0] ) 
            {
                $max = $p;
            }
        }
        return $max;
    }

    protected function getMinXPoint() 
    {
        $min = $this->inputPoints[0];
        foreach( $this->inputPoints as $p ) 
        {
            if ( $p[0] < $min[0] ) 
            {
                $min = $p;
            }
        }
        return $min;
    }

    /**
     * Calculate a distance indicator between the line defined by $start and 
     * $end and an arbitrary $point.
     *
     * The value returned is not the correct distance value, but is sufficient 
     * to determine the point with the maximal distance from the line.
     *
     * The returned distance with positive values ->the point is left of the specified vector, 
     * negative values -> right of it. if value = zero the point 
     * is colinear to the line.
     
     */
    protected function calculateDistanceIndicator( array $start, array $end, array $point ) 
    {
        

        $vLine = array( 
            $end[0] - $start[0],
            $end[1] - $start[1]
        );

        $vPoint = array( 
            $point[0] - $start[0],
            $point[1] - $start[1]
        );

        return ( ( $vPoint[1] * $vLine[0] ) - ( $vPoint[0] * $vLine[1] ) );
    }

    /**
     * Calculate the distance indicator for each given point and return an 
     * array containing the point and the distance indicator. 
     *
     * Only points left of the line will be returned. Every point right of the 
     * line or colinear to the line will be deleted.
      */
    protected function getPointDistanceIndicators( array $start, array $end, array $points ) 
    {
        $resultSet = array();

        foreach( $points as $p ) 
        {
            if ( ( $distance = $this->calculateDistanceIndicator( $start, $end, $p ) ) > 0 ) 
            {
                $resultSet[] = array( 
                    'point'    => $p,
                    'distance' => $distance
                );
            }
            else 
            {
                continue;
            }
        }

        return $resultSet;
    }

    /**
     * Get the point which has the maximum distance from a given line.
     */
    protected function getPointWithMaximumDistanceFromLine( array $pointDistanceSet ) 
    {
        $maxDistance = 0;
        $maxPoint    = null;

        foreach( $pointDistanceSet as $p ) 
        {
            if ( $p['distance'] > $maxDistance )
            {
                $maxDistance = $p['distance'];
                $maxPoint    = $p['point'];
            }
        }

        return $maxPoint;
    }

    /**
     * Extract the points from a point distance set. 
     * 
     * @param array $pointDistanceSet 
     * @return array
     */
    protected function getPointsFromPointDistanceSet( $pointDistanceSet ) 
    {
        $points = array();

        foreach( $pointDistanceSet as $p ) 
        {
            $points[] = $p['point'];
        }

        return $points;
    }

    /**
     * Execute a QuickHull run on the given set of points, using the provided 
     * line as delimiter of the search space.
     *
     * Only points left of the given line will be analyzed. 
     */
    protected function quickHull( array $points, array $start, array $end ) 
    {
        $pointsLeftOfLine = $this->getPointDistanceIndicators( $start, $end, $points );
        $newMaximalPoint = $this->getPointWithMaximumDistanceFromLine( $pointsLeftOfLine );
        
        if ( $newMaximalPoint === null ) 
        {
            
            return array( $end );
        }

        // The new maximal point creates a triangle together with $start and 
        // $end, Everything inside this trianlge can be ignored. Everything 
        // else needs to handled recursively. Because the quickHull invocation 
        // only handles points left of the line we can simply call it for the 
        // different line segements to process the right kind of points.
        $newPoints = $this->getPointsFromPointDistanceSet( $pointsLeftOfLine );
        return array_merge(
            $this->quickHull( $newPoints, $start, $newMaximalPoint ),
            $this->quickHull( $newPoints, $newMaximalPoint, $end )
        );
    }
}
