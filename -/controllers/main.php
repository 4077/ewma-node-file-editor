<?php namespace ewma\nodeFileEditor\controllers;

class Main extends \Controller
{
    public function __create()
    {
        $this->packModels();
        $this->dmap('|');
        $this->unpackModels();
    }

    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function performCallback($name, $data = [])
    {
        $callbacks = $this->d(':callbacks|');

        if (isset($callbacks[$name])) {
            $this->_call($callbacks[$name])->ra($data)->perform();
        }

        return $this;
    }

    public function reset()
    {
        $d = &$this->d('|');

        if ($d['cache']) {
            $d['cache'] = null;

            $this->performCallback('reset');
        }
    }

    public function save()
    {
        $d = &$this->d('|');

        if ($d['cache']) {
            $nodeType = $d['node_type'];
            $targetNode = $d['target_node'];

            $ext = $this->getExtension($nodeType);

            $filePath = abs_path($this->_nodeFilePath(force_l_slash($targetNode), $nodeType) . '.' . $ext);

            write($filePath, $d['cache']);

            $d['cache'] = null;

            $this->performCallback('save');
        }
    }

    public function view()
    {
        $v = $this->v('|');

        $v->assign([
                       'CONTENT' => $this->c('\js\ace~:view|' . $this->_nodeInstance(), [
                           'path' => '>xhr:update|',
                           'mode' => $this->getEditorMode($this->data('node_type')),
                           'code' => $this->getCode()
                       ])
                   ]);

        $this->css();

        $this->widget(':|', [
            'resizableClosestSelector' => $this->data('resizable_closest_selector')
        ]);

        return $v;
    }

    private function getCode()
    {
        $code = $this->d('~:cache|') or
        $code = read($this->getFilePath());

        return $code;
    }

    private function getFilePath()
    {
        $targetNode = $this->data('target_node');
        $nodeType = $this->data('node_type');

        list($modulePath,) = explode(' ', $targetNode);

        if (!$this->app->modules->getByPath($modulePath)) {
            $this->c('\ewma\dev~:createModule', [
                'path'  => $modulePath,
                'reset' => true
            ]);
        }

        $ext = $this->getExtension($nodeType);

        $filePath = abs_path($this->_nodeFilePath(force_l_slash($targetNode), $nodeType) . '.' . $ext);

        if (!file_exists($filePath)) {
            $codeTemplatesNode = $this->n(force_l_slash($this->data('template_node')));

            $templateFilePath = abs_path($codeTemplatesNode->_nodeFilePath() . '.' . $ext);

            $code = read($templateFilePath);

            if ($tokenize = $this->data('tokenize')) {
                $code = \ewma\Data\Data::tokenize($code, $tokenize);
            }

            write($filePath, $code);
        }

        return $filePath;
    }

    private function getExtension($type)
    {
        return $this->dataByType[$type][1];
    }

    private function getEditorMode($type)
    {
        return $this->dataByType[$type][2];
    }

    private $dataByType = [
        // [type => dir, ext, editor_mode]
        'controller' => ['controllers', 'php', 'php'],
        'js'         => ['js', 'js', 'javascript'],
        'css'        => ['css', 'css', 'css'],
        'less'       => ['less', 'less', 'less'],
        'template'   => ['templates', 'tpl', 'smarty']
    ];
}
