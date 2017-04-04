<?php


namespace Acme;


use Fhaculty\Graph\Set\Vertices;
use Fhaculty\Graph\Vertex;
use Graphp\Algorithms\Search\DepthFirst;

class CycleDetector extends DepthFirst
{
    public $visited = [];
    private function iterativeDepthFirstSearch(Vertex $vertex)
    {
        $visited = array();
        $todo = array($vertex);
        while ($vertex = array_shift($todo)) {
            $this->visited[] = $vertex->getId();
            if (!isset($visited[$vertex->getId()])) {
                $visited[$vertex->getId()] = $vertex;
                foreach (array_reverse($this->getVerticesAdjacent($vertex)->getMap(), true) as $vid => $nextVertex) {
                    $todo[] = $nextVertex;
                }
            }
        }

        return new Vertices($visited);
    }

    /**
     * calculates a recursive depth-first search
     *
     * @return Vertices
     */
    public function hasCycle()
    {
        $this->iterativeDepthFirstSearch($this->vertex);
        if(count($this->visited) != count(array_unique($this->visited))){
            return true;
        }else{
            return false;
        }
    }
}