<?php
// Copyright 1999-2017. Parallels IP Holdings GmbH.

use Modules_PleskCloudProviders_Deployer\VpsDeployer;

class IndexController extends pm_Controller_Action
{
    protected $_accessLevel = 'admin';

    public function indexAction()
    {
        $this->view->list = $this->_getNodesList();
    }

    public function dataAction()
    {
        $list = $this->_getNodesList();
        $this->_helper->json($list->fetchData());
    }

    private function _getNodesList()
    {
        $data = [];
        foreach ($this->_getNodesFromStorage() as $node) {
            $data[] = [
                'id' => $node,
                'name' => $node,
            ];
        }
        $list = new pm_View_List_Simple($this->view, $this->_request);
        $list->setData($data);
        $list->setColumns([
            pm_View_List_Simple::COLUMN_SELECTION,
            'name' => [
                'title' => 'Node',
                'searchable' => true,
            ],
        ]);
        $list->setTools([
            [
                'class' => 'sb-add-new',
                'title' => 'Deploy node',
                'link' => \pm_Context::getActionUrl('index', 'add'),
            ],
            [
                'title' => 'Remove',
                'class' => 'sb-remove-selected',
                'execGroupOperation' => $this->_helper->url('remove'),
            ],
        ]);
        $list->setDataUrl(['action' => 'data']);
        return $list;
    }

    private function _getNodesFromStorage()
    {
        return json_decode(pm_Settings::get('nodes', '[]'), true);
    }

    private function _saveNodesToStorage($nodes)
    {
        return pm_Settings::set('nodes', json_encode($nodes));
    }

    public function addAction()
    {
        $deployer = new VpsDeployer();
        $nodeInfo = $deployer->deployNode(null, 'ifgrfc1Q');
        $ip = reset($nodeInfo->getIPv4Addresses());

        $nodes = $this->_getNodesFromStorage();
        $nodes[] = $ip;
        $this->_saveNodesToStorage($nodes);
        $this->_status->addInfo("Node {$ip} is deployed");
        $this->redirect(pm_Context::getActionUrl('index', 'index'), ['prependBase' => false]);
    }

    public function removeAction()
    {
        $redirect = pm_Context::getActionUrl('index', 'index');
        try {
            $nodes = $this->_getNodesFromStorage();
            $nodesToRemove = (array)$this->_getParam('ids');
            foreach ($nodes as $node) {
                if (in_array($node, $nodesToRemove)) {
                    $deployer = new VpsDeployer();
                    $nodeInfo = $deployer->destroyNode($node);
                }
            }
            $nodes = array_diff($nodes, $nodesToRemove);
            $this->_saveNodesToStorage($nodes);
            $this->_helper->json(['status' => 'success', 'statusMessages' => [
                [
                    'status' => 'info',
                    'content' => 'Selected nodes are removed',
                ]
            ]]);
        } catch (Exception $e) {
            $this->_helper->json(['status' => 'success', 'statusMessages' => [
                [
                    'status' => 'error',
                    'content' => $e->getMessage(),
                ]
            ]]);
        }
    }
}
