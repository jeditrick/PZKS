<?php
namespace Acme;

use Fhaculty\Graph\Edge\Base;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Edge\Undirected;
use Fhaculty\Graph\Graph as GraphLib;
use Graphp\Algorithms\ConnectedComponents;
use Graphp\GraphViz\GraphViz;

class Graph extends GraphLib
{
    protected $graphType;

    /**
     * @param mixed $graphType
     */
    public function setGraphType($graphType)
    {
        $this->graphType = $graphType;// 1 - task graph, 0 - system graph
    }

    public function __construct($graphType = null)
    {
        parent::__construct();
        $this->graphType = $graphType;// 1 - task graph, 0 - system graph
    }

    public function addNode($id)
    {
        $node = $this->createVertex($id, true);
        if ($this->graphType == 1) {
            $node->setAttribute('graphviz.shape', 'circle');
        } else {
            $node->setAttribute('graphviz.shape', 'square');
        }

        return $node;
    }

    public function removeNode($id)
    {
        $this->getVertex($id)->destroy();
    }

    public function deleteEdge($node_one_key, $node_two_key)
    {
        $node_one = $this->getVertex($node_one_key);
        $node_two = $this->getVertex($node_two_key);
        $edges = $node_one->getEdges();
        foreach ($edges as $edge) {
            /* @var $edge Base */
            if ($edge->isConnection($node_one, $node_two)) {
                $edge->destroy();
            }
        }
    }

    /**
     * @param $node_one_key
     * @param $node_two_key
     * @return Base
     */
    public function getEdge($node_one_key, $node_two_key)
    {
        $node_one = $this->getVertex($node_one_key);
        $node_two = $this->getVertex($node_two_key);
        $edges = $node_one->getEdges();
        foreach ($edges as $edge) {
            if ($edge->isConnection($node_one, $node_two)) {
                return $edge;
            }
        }
    }

    public function display()
    {
        $graphviz = new GraphViz();
        $graphviz->display($this);
    }

    public function hasCycle()
    {
        $algo = new CycleDetector($this->getVertex(1));

        return $algo->hasCycle();
    }

    public function checkConnectivity()
    {
        $algo = new ConnectedComponents($this);
        $components_count = $algo->getNumberOfComponents();

        return $components_count == 1;
    }

    public function getComponents()
    {
        $algo = new ConnectedComponents($this);

        return $algo->createGraphsComponents();
    }

    public function readFromFile($file)
    {
        $raw_data = explode("\n", file_get_contents(dirname(__DIR__) . '/' . $file));
        foreach ($raw_data as $edge) {
            $nodes = explode(' ', $edge);
            $this->addNode($nodes[0]);
            if (count($nodes) > 1) {
                $this->addNode($nodes[1]);
                if($this->graphType == 1){
                    $edge = new Directed($this->getVertex($nodes[0]), $this->getVertex($nodes[1]));
                } else {
                    $edge = new Undirected($this->getVertex($nodes[0]), $this->getVertex($nodes[1]));
                }
                if (count($nodes) > 2) {
                    $edge->setWeight((int)$nodes[2]);
                }
            }
        }
    }

    public function setNodeWeight($node_id, $node_weight)
    {
        $node = $this->getVertex($node_id);
        $node->setAttribute('weight', $node_weight);
        $node->setAttribute('graphviz.label', $node->getId() . " (" . $node->getAttribute('weight') . ")");
    }

    public function addEdgeWeight($node_one_key, $node_two_key, $weight)
    {
        $node_one = $this->getVertex($node_one_key);
        $node_two = $this->getVertex($node_two_key);
        $edges = $node_one->getEdges();
        foreach ($edges as $edge) {
            if ($edge->isConnection($node_one, $node_two)) {
                $edge->setWeight($weight);
            }
        }
    }

    public function generateGraph(
        $min_node_weight,
        $max_node_weight,
        $node_count,
        $min_edge_weight,
        $max_edge_weight,
        $correlation
    ) {
        $total_nodes_weight = 0;
        foreach (range(0, $node_count - 1) as $node_id) {
            $node_weight = rand($min_node_weight, $max_node_weight);
            $total_nodes_weight += $node_weight;
            $this->addNode($node_id);
            $this->setNodeWeight($node_id, $node_weight);
        }

        $total_edges_weight = (int)($total_nodes_weight / $correlation - $total_nodes_weight);

        while ($total_edges_weight > 0) {
            if ($total_edges_weight < $min_edge_weight) {
                $edge_weight = $total_edges_weight;
            } else {
                $edge_weight = rand($min_edge_weight, min($total_edges_weight, $max_edge_weight));
            }

            $edge_nodes = $this->getNodes($this->getVertices());

            if (count($edge_nodes[0]->getEdgesTo($edge_nodes[1])) > 0) {
                $edge = $edge_nodes[0]->getEdgesTo($edge_nodes[1])->getEdgeFirst();
            } else {
                $edge = $this->addEdge($edge_nodes[0]->getId(), $edge_nodes[1]->getId());
            }

            $edge->setWeight($edge->getWeight() + $edge_weight);
            $total_edges_weight -= $edge_weight;
        }
    }

    private function getNodes($nodes)
    {
        while (true) {
            $node1 = $nodes->getVertexId(rand(0, count($nodes) - 1));
            $node2 = $nodes->getVertexId(rand(0, count($nodes) - 1));
            if (count($node2->getEdgesTo($node1)) == 0 && $node1->getId() < $node2->getId()) {
                return [$node1, $node2];
            }
        }
    }
}
