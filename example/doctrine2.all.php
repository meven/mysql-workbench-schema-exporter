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

// show errors
error_reporting(E_ALL);

// lets stop the time
$start = microtime(true);

// enable autoloading of classes
require_once('../lib/MwbExporter/Core/SplClassLoader.php');
$classLoader = new SplClassLoader();
$classLoader->setIncludePath('../lib');
$classLoader->register();


# create a parser
$parser = new \MwbExporter\Formatter\Doctrine2\Annotation\Parser();

# create a document and parse it
$document = new \MwbExporter\Core\Workbench\Document('data/test.mwb', $parser);

# create a formatter
$formatter = new \MwbExporter\Formatter\Doctrine2\Annotation\Loader(array());

# format the document using the formatter
echo $formatter->visitDocument($document);

# create another formatter
$yamlFormatter = new \MwbExporter\Formatter\Doctrine2\Yaml\Loader(array());

# reformat the document using the second formatter
echo $yamlFormatter->visitDocument($document);

// show some information about used memory
echo (memory_get_peak_usage(true) / 1024 / 1024) . " MB used\n";

// show the time needed to parse the mwb file
$end = microtime(true);
echo  sprintf('%0.3f', $end-$start) . " sec needed\n";