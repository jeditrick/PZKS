<?php
namespace Acme;

use Fhaculty\Graph\Graph as GraphLib;
use Graphp\Algorithms\ConnectedComponents;
use Graphp\GraphViz\GraphViz;

class Graph
{
    protected $graphType;
    const NODE_GENERATION_ATTEMPTS = 10;

    /**
     * @param mixed $graphType
     */
    public function setGraphType($graphType)
    {
        $this->graphType = $graphType;// 1 - task graph, 0 - system graph
    }

    public function __construct()
    {
        $this->graph = new GraphLib();
    }

    public function addNode($id)
    {
        $node  = $this->graph->createVertex($id, true);
        if ($this->graphType == 1) {
            $node->setAttribute('graphviz.shape', 'circle');
        } else {
            $node->setAttribute('graphviz.shape', 'square');
        }
        return $node;
    }

    public function addEdge($node_one_key, $node_two_key)
    {
        if ($this->graphType) {
            return $this->graph->getVertex($node_one_key)->createEdgeTo($this->graph->getVertex($node_two_key));
        } else {
            return $this->graph->getVertex($node_one_key)->createEdge($this->graph->getVertex($node_two_key));
        }
    }

    public function removeNode($id)
    {
        $this->graph->getVertex($id)->destroy();
    }

    public function removeEdge($node_one_key, $node_two_key)
    {
        $node_one = $this->graph->getVertex($node_one_key);
        $node_two = $this->graph->getVertex($node_two_key);
        $edges = $node_one->getEdges();
        foreach ($edges as $edge) {
            if ($edge->isConnection($node_one, $node_two)) {
                $edge->destroy();
            }
        }
    }
    
    public function display()
    {
        $graphviz = new GraphViz();
        $graphviz->display($this->graph);
    }

    public function getEdges()
    {
        return $this->graph->getEdges();
    }

    public function hasCycle()
    {
        $algo = new CycleDetector($this->graph->getVertex(1));
        return $algo->hasCycle();
    }

    public function checkConnectivity()
    {
        $algo = new ConnectedComponents($this->graph);
        $components_count = $algo->getNumberOfComponents();
        return $components_count == 1;
    }

    public function getComponents()
    {
        $algo = new ConnectedComponents($this->graph);
        return $algo->createGraphsComponents();
    }

    public function readFromFile($file)
    {
        $raw_data = explode("\n", file_get_contents(dirname(__DIR__).'/'.$file));
        $new = new Graph();
        $new->graphType = $this->graphType;
        foreach ($raw_data as $edge) {
            $nodes = explode(' ', $edge);
            $new->addNode($nodes[0]);
            $new->addNode($nodes[1]);
            $new->addEdge($nodes[0], $nodes[1]);
        }
        $this->graph = $new->graph;
    }

    public function setNodeWeight($node_id, $node_weight)
    {
        $node = $this->graph->getVertex($node_id);
        $node->setAttribute('weight', $node_weight);
        $node->setAttribute('graphviz.label', $node->getId()." (".$node->getAttribute('weight').")");
    }

    public function addEdgeWeight($node_one_key, $node_two_key, $weight)
    {
        $node_one = $this->graph->getVertex($node_one_key);
        $node_two = $this->graph->getVertex($node_two_key);
        $edges = $node_one->getEdges();
        foreach ($edges as $edge) {
            if ($edge->isConnection($node_one, $node_two)) {
                $edge->setWeight($weight);
            }
        }
    }

    public function generateGraph($min_node_weight, $max_node_weight, $node_count, $min_edge_weight, $max_edge_weight, $coleration)
    {
        $total_nodes_weight = 0;
        foreach (range(0, $node_count-1) as $node_id) {
            $node_weight = rand($min_node_weight, $max_node_weight);
            $total_nodes_weight += $node_weight;
            $this->addNode($node_id);
            $this->setNodeWeight($node_id, $node_weight);
        }

        $total_edges_weight = (int)($total_nodes_weight/$coleration - $total_nodes_weight);

        while($total_edges_weight > 0){
            $edge_weight = rand($min_edge_weight, min($total_edges_weight, $max_edge_weight));
            $edge_nodes = $this->getNodes($this->graph->getVertices());
            $edge = $this->addEdge($edge_nodes[0]->getId(), $edge_nodes[1]->getId());
            $edge->setWeight($edge_weight);
            $total_edges_weight -= $edge_weight;
        }
    }

    private function getNodes($nodes)
    {
        $max_attempts = self::NODE_GENERATION_ATTEMPTS;
        while($max_attempts-- > 0){
            $node1 = $nodes->getVertexId(rand(0, count($nodes)-1));
            $node2 = $nodes->getVertexId(rand(0, count($nodes)-1));
            if(count($node1->getEdgesTo($node2)) == 0 && count($node2->getEdgesTo($node1)) == 0 && $node1->getId() != $node2->getId()){
                return [$node1, $node2];
            }
        }
    }
}
