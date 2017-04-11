<?php


namespace Acme;


use Graphp\Algorithms\ShortestPath\BreadthFirst;

class MyBfs extends BreadthFirst
{
    public function getCriticalPathes($last_node_id)
    {
        $critical_pathes = [];
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
                if($vertexCurrent->getId() == $last_node_id){
                    $critical_pathes[] = $edgesCurrent;
                }
            }

            // untill queue is empty
        } while ($vertexCurrent);

        return $critical_pathes;
    }


}