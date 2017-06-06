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
        $this->nodes = $graph->getVertices();
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


    /**
     * @param bool|false $as_array
     * @return string|array
     */
    public function getSortedNodes($as_array = false)
    {
        return $as_array ? $this->sorted_nodes : json_encode($this->sorted_nodes);
    }


    public function sortByCriticalNodeCount()
    {
        $result = [];
        $nodes = $this->nodes->getMap();
        $last_node_id = max(array_keys($nodes));
        foreach ($nodes as $node) {
            $algorithm = new MyBfs($node);
            $paths = $algorithm->getCriticalPathes($last_node_id);
            $max_nodes_count = 0;
            foreach ($paths as $path) {
                $path_count = 0;
                $path_queue = [];
                foreach ($path as $edge) {
                    foreach ($edge->getVertices() as $edge_node) {
                        if (array_search($edge_node->getId(), $path_queue) === false) {
                            $path_count += 1;
                            $path_queue[]=$edge_node->getId();
                        }
                    }
                }
                $max_nodes_count = max($path_count, $max_nodes_count);
            }
            $result[$node->getId()] = $max_nodes_count > 0 ? $max_nodes_count : 1;
        }
        arsort($result);
        $this->sorted_nodes = $result;
        return $this;
    }

    public function sortByNormalizedCriticalPath()
    {

        $result = [];
        $sorted_by_nodes_count = $this->sortByCriticalNodeCount()->sorted_nodes;
        $sorted_by_weight_count = $this->sortByNodeWeight()->sorted_nodes;

        $max_count = array_values($sorted_by_nodes_count)[0];
        $max_weight = array_values($sorted_by_weight_count)[0];

        foreach($this->nodes->getMap() as $node){
            $result[$node->getId()] = $sorted_by_nodes_count[$node->getId()]/$max_count + $sorted_by_weight_count[$node->getId()]/$max_weight;
        }

        arsort($result);
        $this->sorted_nodes = $result;
        return $this;
    }

    private function sortByNodeWeight()
    {
        $result = [];
        $nodes = $this->nodes->getMap();
        $last_node_id = max(array_keys($nodes));
        foreach ($nodes as $node) {
            $max_nodes_weight = 0;
            $algorithm = new MyBfs($node);
            $paths = $algorithm->getCriticalPathes($last_node_id);
            foreach ($paths as $path) {
                $path_weight = 0;
                $path_queue = [];
                foreach ($path as $edge) {
                    foreach ($edge->getVertices() as $edge_node) {
                        if (array_search($edge_node->getId(), $path_queue) === false) {
                            $path_weight += $edge_node->getAttribute('weight');
                            $path_queue[]=$edge_node->getId();
                        }
                    }
                }
                $max_nodes_weight = max($path_weight, $max_nodes_weight);
            }
            $result[$node->getId()] = $max_nodes_weight != 0 ? $max_nodes_weight : $node->getAttribute('weight');
        }
        arsort($result);
        $this->sorted_nodes = $result;
        return $this;
    }
}
