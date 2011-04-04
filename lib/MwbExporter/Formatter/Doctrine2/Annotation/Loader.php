<?php
/*
 *  The MIT License
 *
 *  Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace MwbExporter\Formatter\Doctrine2\Annotation;

class Loader implements \MwbExporter\Core\IFormatter
{
    public function __construct(array $setup = array()){
        \MwbExporter\Core\Registry::set('config', $setup);
    }
    
    public function useDatatypeConverter($type, \MwbExporter\Core\Model\Column $column){
        return DatatypeConverter::getType($type, $column);
    }

    public function visitDocument(\MwbExporter\Core\Workbench\Document $node){
        return $this->visitPhysicalModel($node->getPhysicalModel());
    }
    
    public function visitPhysicalModel(\MwbExporter\Core\Model\PhysicalModel $node){
        return $this->visitCatalog($node->getCatalog());
    }
   
    public function visitCatalog(\MwbExporter\Core\Model\Catalog $node){
        return $this->visitSchemas($node->getSchemas());
    }
    
    public function visitColumn(\MwbExporter\Core\Model\Column $node){
        return $node->display();
    }
    
    public function visitColumns(\MwbExporter\Core\Model\Columns $node){
        return $node->display();
    }
    
    public function visitForeignKey(\MwbExporter\Core\Model\ForeignKey $node){
        return $node->display();
    }
    
    public function visitForeignKeys(\MwbExporter\Core\Model\ForeignKeys $node){
        return $node->display();
    }
    
    public function visitIndex(\MwbExporter\Core\Model\Index $node){
        return $node->display();
    }
    
    public function visitIndices(\MwbExporter\Core\Model\Indices $node){    
        return $node->display();
    }
    
    public function visitSchema(\MwbExporter\Core\Model\Schema $node){
        return $node->display();
    }
    
    public function visitSchemas(\MwbExporter\Core\Model\Schemas $node){
        $return = array();

        foreach($node->getSchemas() as $schema){
            $return[] = $this->visitSchema($schema);
        }

        return implode("\n", $return);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         ($node);
    }
    
    public function visitTable(\MwbExporter\Core\Model\Table $node){
        return $node->display();
    }
    
    public function visitTables(\MwbExporter\Core\Model\Tables $node){
        return $node->display();
    }
    
    public function visitView(\MwbExporter\Core\Model\View $node){
        return $node->display();
    }
    
    public function visitViews(\MwbExporter\Core\Model\Views $node){
        return $node->display();
    }
}