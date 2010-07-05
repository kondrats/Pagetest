<?php
include 'common.inc';
include 'domains.inc';

// walk through the requests and group them by domain
$requestsFv;
$breakdownFv = getDomainBreakdown($id, $testPath, $run, 0, $requestsFv);
$breakdownRv = array();
$requestsRv = array();
if( (int)$test[test][fvonly] == 0 )
    $breakdownRv = getDomainBreakdown($id, $testPath, $run, 1, $requestsRv);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Web page performance test domain breakdown</title>
        <meta http-equiv="charset" content="iso-8859-1">
        <meta name="keywords" content="Performance, Optimization, Pagetest, Page Design, CDN, Content Distribution Network">
        <meta name="description" content="Speed up the performance of your web pages with an automated analysis">
        <meta name="author" content="Patrick Meenan">

        <style type="text/css">
            <?php 
                include 'pagestyle.css'; 
            ?>
            td {
                text-align:center; 
                vertical-align:top; 
                padding:1em;
            }

            div.bar {
                height:12px; 
                margin-top:auto; 
                margin-bottom:auto;
            }

            td.legend {
                white-space:nowrap; 
                text-align:left; 
                vertical-align:top; 
                padding:0;
            }
        </style>
        
        <!--Load the AJAX API-->
        <script type="text/javascript" src="http://www.google.com/jsapi"></script>
        <script type="text/javascript">
    
        // Load the Visualization API and the table package.
        google.load('visualization', '1', {'packages':['table']});
        google.setOnLoadCallback(drawTable);
        function drawTable() {
            var dataFv = new google.visualization.DataTable();
            dataFv.addColumn('string', 'Domain');
            dataFv.addColumn('number', 'Requests');
            dataFv.addColumn('number', 'Bytes');
            dataFv.addRows(<?php echo count($breakdownFv); ?>);
            <?php
            $index = 0;
            ksort($breakdownFv);
            foreach($breakdownFv as $domain => $data)
            {
                $domain = strrev($domain);
                echo "dataFv.setValue($index, 0, '$domain');\n";
                echo "dataFv.setValue($index, 1, {$data['requests']});\n";
                echo "dataFv.setValue($index, 2, {$data['bytes']});\n";
                $index++;
            }
            ?>

            var viewRequestsFv = new google.visualization.DataView(dataFv);
            viewRequestsFv.setColumns([0, 1]);
            
            var tableRequestsFv = new google.visualization.Table(document.getElementById('tableRequestsFv_div'));
            tableRequestsFv.draw(viewRequestsFv, {showRowNumber: false, sortColumn: 1, sortAscending: false});

            var viewBytesFv = new google.visualization.DataView(dataFv);
            viewBytesFv.setColumns([0, 2]);
            
            var tableBytesFv = new google.visualization.Table(document.getElementById('tableBytesFv_div'));
            tableBytesFv.draw(viewBytesFv, {showRowNumber: false, sortColumn: 1, sortAscending: false});

            <?php if( count($breakdownRv) ) { ?>
                var dataRv = new google.visualization.DataTable();
                dataRv.addColumn('string', 'Domain');
                dataRv.addColumn('number', 'Requests');
                dataRv.addColumn('number', 'Bytes');
                dataRv.addRows(<?php echo count($breakdownRv); ?>);
                <?php
                $index = 0;
                ksort($breakdownRv);
                foreach($breakdownRv as $domain => $data)
                {
                    $domain = strrev($domain);
                    echo "dataRv.setValue($index, 0, '$domain');\n";
                    echo "dataRv.setValue($index, 1, {$data['requests']});\n";
                    echo "dataRv.setValue($index, 2, {$data['bytes']});\n";
                    $index++;
                }
                ?>

                var viewRequestsRv = new google.visualization.DataView(dataRv);
                viewRequestsRv.setColumns([0, 1]);
                
                var tableRequestsRv = new google.visualization.Table(document.getElementById('tableRequestsRv_div'));
                tableRequestsRv.draw(viewRequestsRv, {showRowNumber: false, sortColumn: 1, sortAscending: false});

                var viewBytesRv = new google.visualization.DataView(dataRv);
                viewBytesRv.setColumns([0, 2]);
                
                var tableBytesRv = new google.visualization.Table(document.getElementById('tableBytesRv_div'));
                tableBytesRv.draw(viewBytesRv, {showRowNumber: false, sortColumn: 1, sortAscending: false});
            <?php } ?>
        }
        </script>
        <?php include('analytics.inc'); ?>
    </head>
    <body>
        <div class="page">
            <?php
            $tab = 'Test Result';
            $subtab = 'Domain Breakdown';
            include 'header.inc';
            ?>
            <div class="content">
                <p>Web page domain breakdown for <b><a rel="nofollow" href=<?php echo '"' . $url . '"';?>><?php echo $url;?></a></b><br>
                Test completed - <?php echo $test[test][completeTime];?> from <?php echo $test[test][location];?>
                <?php
                if( (int)$test[test][authenticated] == 1)
                    echo '<br><b>Authenticated: ' . $login . '</b>';
                ?>
                <?php
                if( (int)$test[test][connections] !== 0)
                     echo '<b>' . $test[test][connections] . ' Browser connections</b><br>';
                ?>
                </p>
                <br>
                <br>
                <table align="center">
                    <tr>
                        <th colspan="2">
                        <h2>Content breakdown by domain (First  View)</h2>
                        </th>
                    </tr>
                    <tr>
                        <td>
                            <img style="width:500px; height:500px;" src="/domainPie.php?width=500&height=500&type=Requests&cached=0<?php echo "&test=$id&run=$run"; ?>"></img>
                        </td>
                        <td>
                            <img style="width:500px; height:500px;" src="/domainPie.php?width=500&height=500&type=Bytes&cached=0<?php echo "&test=$id&run=$run"; ?>"></img>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div id="tableRequestsFv_div" style="width: 100%;"></div>
                        </td>
                        <td>
                            <div id="tableBytesFv_div" style="width: 100%;"></div>
                        </td>
                    </tr>
                </table>

                <?php if( count($breakdownRv) ) { ?>
                <br><hr><br>
                <table align="center">
                    <tr>
                        <th colspan="2">
                        <h2>Content breakdown by domain (Repeat  View)</h2>
                        </th>
                    </tr>
                    <tr>
                        <td>
                            <img style="width:500px; height:500px;" src="/domainPie.php?width=500&height=500&type=Requests&cached=1<?php echo "&test=$id&run=$run"; ?>"></img>
                        </td>
                        <td>
                            <img style="width:500px; height:500px;" src="/domainPie.php?width=500&height=500&type=Bytes&cached=1<?php echo "&test=$id&run=$run"; ?>"></img>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div id="tableRequestsRv_div" style="width: 100%;"></div>
                        </td>
                        <td>
                            <div id="tableBytesRv_div" style="width: 100%;"></div>
                        </td>
                    </tr>
                </table>
                <?php } ?>
            </div>
        </div>
    </body>
</html>