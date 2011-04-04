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

namespace MwbExporter\Formatter\Doctrine2\Yaml;

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
        $colConfig = $node->getConfig();
        $link = $node->getLink();
        
        $return = array();

        // set name of column
        $return[] = '    ' . $colConfig['name'] . ':';

        // set datatype of column
        $return[] = '      type: ' . $this->useDatatypeConverter((isset($link['simpleType']) ? $link['simpleType'] : $link['userType']), $node);

        if($node->isPrimary()){
            $return[] = '      primary: true';
        }

        // check for not null column
        if(isset($colConfig['isNotNull']) && $colConfig['isNotNull'] == 1){
            $return[] = '      notnull: true';
        }
        
        // check for auto increment column
        if(isset($colConfig['autoIncrement']) && $colConfig['autoIncrement'] == 1){
            $return[] = '      autoincrement: true';
        }

        // set default value
        if(isset($colConfig['defaultValue']) && $colConfig['defaultValue'] != '' && $colConfig['defaultValue'] != 'NULL'){
            $return[] = '      default: ' . $colConfig['defaultValue'];
        }

        $data = $node->getData();
        // iterate on column flags
        foreach($data->xpath("value[@key='flags']/value") as $flag){
            $return[] = '      ' . strtolower($flag) . ': true';
        }

        // return yaml representation of column
        return implode("\n", $return);
    }
    
    public function visitColumns(\MwbExporter\Core\Model\Columns $node){}
    
    public function visitForeignKey(\MwbExporter\Core\Model\ForeignKey $node){
        $fkConfig = $node->getConfig();
        $return = array();
        $return[] = '    ' . $node->getReferencedTable()->getModelName() . ':';
        $return[] = '      class: ' . $node->getReferencedTable()->getModelName();

        $ownerColumn = $node->getData()->xpath("value[@key='columns']");
        $return[] = '      local: ' . \MwbExporter\Core\Registry::get((string) $ownerColumn[0]->link)->getColumnName();
        
        $referencedColumn = $node->getData()->xpath("value[@key='referencedColumns']");
        $return[] = '      foreign: ' . \MwbExporter\Core\Registry::get((string) $referencedColumn[0]->link)->getColumnName();

        if((int)$fkConfig['many'] === 1){
            $return[] = '      foreignAlias: ' . \MwbExporter\Helper\Pluralizer::pluralize($node->getOwningTable()->getModelName());
        } else {
            $return[] = '      foreignAlias: ' . $node->getOwningTable()->getModelName();
        }

        $return[] = '      onDelete: ' . strtolower($fkConfig['deleteRule']);
        $return[] = '      onUpdate: ' . strtolower($fkConfig['updateRule']);

        return implode("\n", $return);
    }
    
    public function visitForeignKeys(\MwbExporter\Core\Model\ForeignKeys $node){}
    
    public function visitIndex(\MwbExporter\Core\Model\Index $node){
        $idxConfig = $node->getConfig();
        $return = array();
        $return[] = '    ' . $idxConfig['name'] . ':';
        $tmp = '      fields: [';
        foreach($node->getReferencedColumn() as $refColumn){
            $tmp .= $refColumn->getColumnName() . ',';
        }
        $return[] = substr($tmp, 0, -1) . ']';

        // disable type: index for foreign key indexes
        if(strtolower($idxConfig['indexType']) !== 'index') {
            $return[] = '      type: ' . strtolower($idxConfig['indexType']);
        }

        return implode("\n", $return);
    }
    
    public function visitIndices(\MwbExporter\Core\Model\Indices $node){}
    
    public function visitSchema(\MwbExporter\Core\Model\Schema $node){
        $return = array();
        $return[] = $this->visitTables($node->getTables());
        return implode("\n", $return);
    }
    
    public function visitSchemas(\MwbExporter\Core\Model\Schemas $node){
        $return = array();

        foreach($node->getSchemas() as $schema){
            $return[] = $this->visitSchema($schema);
        }

        return implode("\n", $return);
    }
    
    public function visitTable(\MwbExporter\Core\Model\Table $node){
        $tableConfig = $node->getConfig();
        
        $return = array();
        $return[] = '\\Entity\\' . $node->getModelName() . ':';

        $return[] = '  type: Entity';

        // check if schema name has to be included
        $config = \MwbExporter\Core\Registry::get('config');
        if(isset($config['extendTableNameWithSchemaName']) && $config['extendTableNameWithSchemaName']){
            // $schemaname = table->tables->schema->getName()
            $schemaName = $node->getParent()->getParent()->getName();
            $return[] = '  table: ' . $schemaName . '.' . $node->getRawTableName();
        } else {
            
            // add table name if necessary
            if($node->getModelName() !== ucfirst($node->getRawTableName())){
                $return[] = '  table: ' . $node->getRawTableName();
            }
        }

        $return[] = $this->visitColumns($node->getColumns());

        // add relations
        if(count($node->getRelations()) > 0){
            $return[] = '  relations:';

            foreach($node->getRelations() as $relation){
                $return[] = $this->visitForeignKey($relation);
            }
        }

        // add indices
        if(count($node->getIndexes()) > 0){
            $return[] = '  indexes:';

            foreach($node->getIndexes() as $index){
                $return[] = $this->visitIndex($index);
            }
        }

        $return[] = '  options:';
        $return[] = '    charset: ' . $tableConfig['defaultCharacterSetName'];
        $return[] = '    type: ' . $tableConfig['tableEngine'];

        // add empty line behind table
        $return[] = '';

        return implode("\n", $return);
    }
    
    public function visitTables(\MwbExporter\Core\Model\Tables $node){
        $return = array();

        foreach($node->getTables() as $table){
            $return[] = $this->visitTable($table);
        }

        return implode("\n", $return);
    }
    
    public function visitView(\MwbExporter\Core\Model\View $node){
        $return = array();
        $return[] = $node->getModelName() . ':';
        $return[] = $node->columns->display();
        $return[] = '';

        return implode("\n", $return);
    }
    
    public function visitViews(\MwbExporter\Core\Model\Views $node){
        $return = array();

        foreach($node->getViews() as $view){
            $return[] = $this->visitView($view);
        }

        return implode("\n", $return);
    }
}