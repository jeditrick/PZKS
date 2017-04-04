<?php
namespace Acme;

use Fhaculty\Graph\Graph as GraphLib;
use Graphp\Algorithms\ConnectedComponents;
use Graphp\GraphViz\GraphViz;

class Graph
{
    protected $graphType;

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
        $node  = $this->graph->createVertex($id);
        if($this->graphType == 1){
            $node->setAttribute('graphviz.shape', 'circle');
        }else{
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
}
