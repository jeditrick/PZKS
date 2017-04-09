<?php


namespace Acme;


use Graphp\Algorithms\ShortestPath\BreadthFirst;

class MyBfs extends BreadthFirst
{
    public function getEdgesMap()
    {
        $vertexQueue = array();
        $edges = array();

        // $edges[$this->vertex->getId()] = array();

        $vertexCurrent = $this->vertex;
        $edgesCurrent = array();

        do {
            foreach ($vertexCurrent->getEdgesOut() as $edge) {
                $vertexTarget = $edge->getVertexToFrom($vertexCurrent);
                $vid = $vertexTarget->getId();
                $vertexQueue []= $vertexTarget;
                $edges[$vid] = array_merge($edgesCurrent, array($edge));
            }

            // get next from queue
            $vertexCurrent = array_shift($vertexQueue);
            if ($vertexCurrent) {
                $edgesCurrent = $edges[$vertexCurrent->getId()];
            }
            // untill queue is empty
        } while ($vertexCurrent);

        return $edges;
    }


}