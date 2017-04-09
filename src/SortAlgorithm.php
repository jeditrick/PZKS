<?php


namespace Acme;

use Graphp\Algorithms\ShortestPath\BreadthFirst;

class SortAlgorithm
{
    private $nodes;

    private $sorted_nodes;

    private $graph;


    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
        $this->nodes = $graph->graph->getVertices();
    }

    public function sortByOutgoingEdges()
    {
        $nodes = $this->nodes->getMap();
        $result = [];
        foreach ($nodes as $node) {
            $result[$node->getId()] = count($node->getEdgesOut());
        }
        arsort($result);
        $this->sorted_nodes = $result;
        return $this;
    }


    public function getSortedNodes()
    {
        return json_encode($this->sorted_nodes);
    }


    public function sortByCriticalNodeCount()
    {
        $result = [];
        $nodes = $this->nodes->getMap();
        $last_node_id = max(array_keys($nodes));
        foreach ($nodes as $node) {
            $algorithm = new MyBfs($node);
            $paths = $algorithm->getEdgesMap();
            if (isset($paths[$last_node_id])) {
                $result[$node->getId()] = count($paths[$last_node_id]);
            } else {
                $result[$node->getId()] = 1;
            }
        }
        arsort($result);
        $this->sorted_nodes = $result;
        return $this;
    }
}
