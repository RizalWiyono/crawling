<?php
    namespace PhpmlExercise\Classification;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IIR</title>



    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

        <!-- Style -->
    <link rel="stylesheet" href="src/css/style.css">
</head>
<body>
        <div class="menu-grid" style="display: flex;">
            <nav class="navbar navbar-expand-lg navbar-light mb-4" style="width: 145px; position: fixed; height: 100%; background-color: #20801d; border-bottom: 2px solid #386a99;" align="center">
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar mr-auto" style="list-style: none; display: block;">
                        <li class="nav-item active">
                            <a class="nav-link font-weight-bold" style="color: #FFF !important;" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" style="color: #FFF !important;" href="#">Evaluasi</a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="menu mt-5" style="margin-left: 145px;">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th colspan="4">Dice</th>
                                    </tr>
                                    <tr>
                                        <th scope="col">Tweets</th>
                                        <th scope="col">Original</th>
                                        <th scope="col">Sistem</th>
                                        <th scope="col">Valid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    error_reporting(0);
                                    $arrayDice = [];
                                    $arrayTargetDice = [];
                                    $arrayStringDice = [];
                                    $no=0;
                                    include 'src/connection/connection.php'; 
                                    $query_data  = mysqli_query($connect, "SELECT * FROM tweets");
                                    while($row = mysqli_fetch_array($query_data)){ 
                                    array_push($arrayDice, $row["tweets"]); 
                                    array_push($arrayTargetDice, $row["simil3"]);
                                    array_push($arrayStringDice, $row["tweets"],); ?>
                                    
                                    <?php $no++; }
                                    // PHP-ML 
                                    require_once __DIR__ . '/vendor/autoload.php';

                                    use Phpml\FeatureExtraction\TokenCountVectorizer;
                                    use Phpml\Tokenization\WhitespaceTokenizer;
                                    use Phpml\CrossValidation\StratifiedRandomSplit;
                                    use Phpml\Dataset\ArrayDataset;

                                    $tf = new TokenCountVectorizer(new WhitespaceTokenizer());
                                    $tf->fit($arrayDice);
                                    $tf->transform($arrayDice);
                                    $vocabulary = $tf->getVocabulary();

                                    $count = count($arrayDice);

                                    $dataset = new ArrayDataset(
                                        $samples = $arrayDice,
                                        $targets = $arrayTargetDice
                                    );

                                    $datasets = new ArrayDataset(
                                        $samples = $arrayStringDice,
                                        $targets = $arrayTargetDice
                                    );

                                    $dataset = new StratifiedRandomSplit($dataset, 0.2, 1234);
                                    $datasets = new StratifiedRandomSplit($datasets, 0.2, 1234);
                                    $xTrainData = $dataset->getTrainSamples();
                                    $yTrainData = $dataset->getTrainLabels();
                                    $xTestData = $dataset->getTestSamples();
                                    $yTestData = $dataset->getTestLabels();

                                    // Menconvert dari hufuf ke angka
                                    $xTrainEncoded = labelEncode($xTrainData);
                                    $xTestEncoded = labelEncode($xTestData);

                                    
                                    use Phpml\Classification\DecisionTree;
                                    $model = new DecisionTree();
                                    $model->train($xTrainEncoded, $yTrainData);
                                    // print_r($model);
                                    
                                    // Testing
                                    $prediction = [];
                                    for($i = 0;$i < count($xTestEncoded);$i++){
                                        $prediction[$i] = $model->predict($xTestEncoded[$i]);
                                    }

                                    $newData = (array)$datasets;
                                    $arrData = [];
                                    foreach($newData as $item){
                                        array_push($arrData, $item);
                                    }

                                    $no=0;
                                    foreach($arrData[1] as $row){
                                        $valueSentiment = $arrData[3][$no];
                                        include 'src/connection/connection.php';
                                        $sql_param  = mysqli_query($connect, "SELECT * FROM tweets WHERE tweets='$row' LIMIT 1");
                                        while($rows = mysqli_fetch_array($sql_param)){

                                            if(round($valueSentiment,0) == 0){
                                                $sentimentT1 = "Netral";
                                            }elseif(round($valueSentiment,0) == 0.5){
                                                $sentimentT1 = "Negatif";
                                            }elseif(round($valueSentiment,0) == 1){
                                                $sentimentT1 = "Positif";
                                            }

                                            if(round($rows["simil3"],0) == 0){
                                                $sentimentT2 = "Netral";
                                            }elseif(round($rows["simil3"],0) == 0.5){
                                                $sentimentT2 = "Negatif";
                                            }elseif(round($rows["simil3"],0) == 1){
                                                $sentimentT2 = "Positif";
                                            } ?>
                                    <tr>
                                        <th scope="row"><?=$row?></th>
                                        <td><?=$sentimentT1?></td>
                                        <td><?=$sentimentT2?></td>
                                        <?php
                                        if($sentimentT1 == $sentimentT2){ 
                                            $acDice += 1;
                                            ?>
                                            <td>V</td>
                                        <?php }else{ ?>
                                            <td>X</td>
                                        <?php } ?>
                                    </tr>
                                    
                                    <?php } $no++; } ?>
                                    <tr style="font-weight: 700;">
                                        <td>Jumlah Data Testing:</td>
                                        <td colspan="3"><?=count($arrData[1])?></td>
                                    </tr>
                                    <tr style="font-weight: 700;">
                                        <td>Jumlah Data Valid:</td>
                                        <td colspan="3"><?=$acDice?></td>
                                    </tr>
                                    <tr style="font-weight: 700;">
                                        <td>Jumlah Data Tidak Valid:</td>
                                        <td colspan="3"><?=count($arrData[1])-$acDice?></td>
                                    </tr>
                                    <tr style="font-weight: 700;">
                                        <td>Akurasi:</td>
                                        <td colspan="3"><?=$acDice/count($arrData[1])*100?>%</td>
                                    </tr>
                                </tbody>
                            </table>
                            <hr>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th colspan="4">Asymmetric</th>
                                    </tr>
                                    <tr>
                                    <th scope="col">Tweets</th>
                                    <th scope="col">Original</th>
                                    <th scope="col">Sistem</th>
                                    <th scope="col">Valid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $arrayAsymmetric = [];
                                    $arrayTargetAsymmetric = [];
                                    $arrayStringAsymmetric = [];
                                    $no=0;
                                    include 'src/connection/connection.php'; 
                                    $query_data  = mysqli_query($connect, "SELECT * FROM tweets");
                                    while($row = mysqli_fetch_array($query_data)){ 
                                    array_push($arrayAsymmetric, $row["tweets"]); 
                                    array_push($arrayTargetAsymmetric, $row["simil2"]);
                                    array_push($arrayStringAsymmetric, $row["tweets"],); ?>
                                    
                                    <?php $no++; } 
                                    require_once __DIR__ . '/vendor/autoload.php';

                                    $tf->fit($arrayAsymmetric);
                                    $tf->transform($arrayAsymmetric);
                                    $vocabulary = $tf->getVocabulary();

                                    $count = count($arrayAsymmetric);

                                    $dataset = new ArrayDataset(
                                        $samples = $arrayAsymmetric,
                                        $targets = $arrayTargetAsymmetric
                                    );

                                    $datasets = new ArrayDataset(
                                        $samples = $arrayStringAsymmetric,
                                        $targets = $arrayTargetAsymmetric
                                    );

                                    $dataset = new StratifiedRandomSplit($dataset, 0.2, 1234);
                                    $datasets = new StratifiedRandomSplit($datasets, 0.2, 1234);
                                    $xTrainData = $dataset->getTrainSamples();
                                    $yTrainData = $dataset->getTrainLabels();
                                    $xTestData = $dataset->getTestSamples();
                                    $yTestData = $dataset->getTestLabels();

                                    $xTrainEncoded = labelEncode($xTrainData);
                                    $xTestEncoded = labelEncode($xTestData);

                                    
                                    $model = new DecisionTree();
                                    $model->train($xTrainEncoded, $yTrainData);
                                    
                                    $prediction = [];
                                    for($i = 0;$i < count($xTestEncoded);$i++){
                                        $prediction[$i] = $model->predict($xTestEncoded[$i]);
                                    }

                                    $newData = (array)$datasets;
                                    $arrData = [];
                                    foreach($newData as $item){
                                        array_push($arrData, $item);
                                    }
                                    // echo "<pre>";
                                    // print_r($arrData);
                                    // echo "</pre>";

                                    $no=0;
                                    foreach($arrData[1] as $row){
                                        $valueSentiment = $arrData[3][$no];
                                        include 'src/connection/connection.php';
                                        $sql_param  = mysqli_query($connect, "SELECT * FROM tweets WHERE tweets='$row' LIMIT 1");
                                        while($rows = mysqli_fetch_array($sql_param)){

                                            if(round($valueSentiment,0) == 0){
                                                $sentimentT1 = "Netral";
                                            }elseif(round($valueSentiment,0) == 0.5){
                                                $sentimentT1 = "Negatif";
                                            }elseif(round($valueSentiment,0) == 1){
                                                $sentimentT1 = "Positif";
                                            }

                                            if(round($rows["simil2"],0) == 0){
                                                $sentimentT2 = "Netral";
                                            }elseif(round($rows["simil2"],0) == 0.5){
                                                $sentimentT2 = "Negatif";
                                            }elseif(round($rows["simil2"],0) == 1){
                                                $sentimentT2 = "Positif";
                                            }
                                    ?>
                                    <tr>
                                        <th scope="row"><?=$row?></th>
                                        <td><?=$sentimentT1?></td>
                                        <td><?=$sentimentT2?></td>
                                        <?php
                                        if($sentimentT1 == $sentimentT2){ 
                                            $acAsymmetric += 1;
                                            ?>
                                            <td>V</td>
                                        <?php }else{ ?>
                                            <td>X</td>
                                        <?php } ?>
                                    </tr>
                                    <?php } $no++; } ?>
                                    <tr style="font-weight: 700;">
                                        <td>Jumlah Data Testing:</td>
                                        <td colspan="3"><?=count($arrData[1])?></td>
                                    </tr>
                                    <tr style="font-weight: 700;">
                                        <td>Jumlah Data Valid:</td>
                                        <td colspan="3"><?=$acAsymmetric?></td>
                                    </tr>
                                    <tr style="font-weight: 700;">
                                        <td>Jumlah Data Tidak Valid:</td>
                                        <td colspan="3"><?=count($arrData[1])-$acAsymmetric?></td>
                                    </tr>
                                    <tr style="font-weight: 700;">
                                        <td>Akurasi:</td>
                                        <td colspan="3"><?=$acAsymmetric/count($arrData[1])*100?>%</td>
                                    </tr>
                                </tbody>
                            </table>
                            <hr>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th colspan="4">Overlap</th>
                                    </tr>
                                    <tr>
                                    <th scope="col">Tweets</th>
                                    <th scope="col">Original</th>
                                    <th scope="col">Sistem</th>
                                    <th scope="col">Valid</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $arrayOverlap = [];
                                    $arrayTargetOverlap = [];
                                    $arrayStringOverlap = [];

                                    $no = 0;
                                    include 'src/connection/connection.php'; 
                                    $queryData  = mysqli_query($connect, "SELECT * FROM tweets");
                                    while($row = mysqli_fetch_array($queryData)){ 
                                        array_push($arrayOverlap, $row["tweets"]); 
                                        array_push($arrayTargetOverlap, $row["simil1"]);
                                        array_push($arrayStringOverlap, $row["tweets"]);?>
                                    
                                    <?php $no++; } 
                                    
                                    $tf->fit($arrayOverlap);
                                    $tf->transform($arrayOverlap);
                                    $vocabulary = $tf->getVocabulary();

                                    $count = count($arrayOverlap);

                                    $dataset = new ArrayDataset(
                                        $samples = $arrayOverlap,
                                        $targets = $arrayTargetOverlap
                                    );

                                    $datasets = new ArrayDataset(
                                        $samples = $arrayStringOverlap,
                                        $targets = $arrayTargetOverlap
                                    );

                                    $dataset = new StratifiedRandomSplit($dataset, 0.2, 1234);
                                    $datasets = new StratifiedRandomSplit($datasets, 0.2, 1234);

                                    $xTrainData = $dataset->getTrainSamples();
                                    $yTrainData = $dataset->getTrainLabels();

                                    $xTestData = $dataset->getTestSamples();
                                    $yTestData = $dataset->getTestLabels();

                                    use Phpml\Preprocessing\LabelEncoder;

                                    $xTrainEncoded = labelEncode($xTrainData);
                                    $xTestEncoded = labelEncode($xTestData);

                                    // Data Training
                                    $model = new DecisionTree();
                                    $model->train($xTrainEncoded, $yTrainData);
                                    
                                    // Data Testing
                                    $prediction = [];
                                    for($i = 0;$i < count($xTestEncoded);$i++){
                                        $prediction[$i] = $model->predict($xTestEncoded[$i]);
                                    }

                                    $newData = (array)$datasets;
                                    $arrData = [];
                                    foreach($newData as $item){
                                        array_push($arrData, $item);
                                    }

                                    $no=0;
                                    foreach($arrData[1] as $row){
                                        error_reporting(0);
                                        $valueSentiment = $arrData[3][$no];
                                        include 'src/connection/connection.php';
                                        $sql_param  = mysqli_query($connect, "SELECT * FROM tweets WHERE tweets='$row' LIMIT 1");
                                        while($rows = mysqli_fetch_array($sql_param)){

                                            if(round($valueSentiment,0) == 0){
                                                $sentimentT1 = "Netral";
                                            }elseif(round($valueSentiment,0) == 0.5){
                                                $sentimentT1 = "Negatif";
                                            }elseif(round($valueSentiment,0) == 1){
                                                $sentimentT1 = "Positif";
                                            }

                                            if(round($rows["simil1"],0) == 0){
                                                $sentimentT2 = "Netral";
                                            }elseif(round($rows["simil1"],0) == 0.5){
                                                $sentimentT2 = "Negatif";
                                            }elseif(round($rows["simil1"],0) == 1){
                                                $sentimentT2 = "Positif";
                                            }
                                    ?>
                                    <tr>
                                        <th scope="row"><?=$row?></th>
                                        <td><?=$sentimentT1?></td>
                                        <td><?=$sentimentT2?></td>
                                        <?php
                                        if($sentimentT1 == $sentimentT2){ 
                                            $acOverlap += 1;
                                            ?>
                                            <td>V</td>
                                        <?php }else{ ?>
                                            <td>X</td>
                                        <?php } ?>
                                    </tr>
                                    <?php } $no++; } ?>
                                    <tr style="font-weight: 700;">
                                        <td>Jumlah Data Testing:</td>
                                        <td colspan="3"><?=count($arrData[1])?></td>
                                    </tr>
                                    <tr style="font-weight: 700;">
                                        <td>Jumlah Data Valid:</td>
                                        <td colspan="3"><?=$acOverlap?></td>
                                    </tr>
                                    <tr style="font-weight: 700;">
                                        <td>Jumlah Data Tidak Valid:</td>
                                        <td colspan="3"><?=count($arrData[1])-$acOverlap?></td>
                                    </tr>
                                    <tr style="font-weight: 700;">
                                        <td>Akurasi:</td>
                                        <td colspan="3"><?=$acOverlap/count($arrData[1])*100?>%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        function labelEncode($xData){
            $xDataProcessed = [];
            $colNum = count($xData[0]);
            for($i = 0;$i < $colNum;$i++){
                $colData = array_column($xData, $i);
                $labelEncoder = new LabelEncoder();
                $target = [];
                $labelEncoder->fit($colData, $target);
                $labels = $labelEncoder->classes();
                for($j = 0;$j < count($xData);$j++){
                    $xDataProcessed[$j][$i] = array_search($xData[$j][$i], $labels);
                }
            }
            return $xDataProcessed;
        }
        ?>
</body>
</html>