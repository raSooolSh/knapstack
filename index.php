<?php
session_start();
$Errors = [];
$results = [];
    //-------get codes after recive POST form-------//
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //----validate recives input values--------//
    if (!is_numeric($_POST['capecity']) && $_POST['capecity'] < 0) {
        array_push($Errors, 'مقدار حجم کوله نا معتبر است');
    }
    if (!isset($_POST['object'])) {
        array_push($Errors, 'حداقل به یک شی نیاز داریم');
    } else {
        foreach ($_POST['object'] as $key => $object) {
            if (!isset($object['name']) or is_null($object['name']) or trim($object['name']) == '') {
                array_push($Errors, "شی $key نیاز به یک نام دارد");
            }
            if (!isset($object['width']) || is_null($object['width']) || !is_numeric($object['width']) || $object['width'] < 0) {
                array_push($Errors, "وزن شی $key نا معتبر است");
            }
            if (!isset($object['value']) || is_null($object['value']) || !is_numeric($object['value']) || $object['value'] < 0) {
                array_push($Errors, "ارزش شی $key نا معتبر است");
            }
        }
    }
    //----set session errors with validate error------//
    $_SESSION['Errors'] = $Errors;

    //-----get countinue codes if validate have not Error-----//
    if (!isset($Errors[0])) {
        $capecity = $_POST['capecity'];
        $objectsNumber = count($_POST['object']);
        //----add index 0 to objects----//
        $_POST['object'][0] = ['name' => '', 'width' => '', 'value' => ''];
        $objects = $_POST['object'];
        //--------create a table for recive best score of backpack----//
        for ($m = 0; $m <= $capecity; $m++) {
            for ($n = 0; $n <= $objectsNumber; $n++) {
                if ($m == 0 || $n == 0) {
                    $table[$n][$m] = 0;
                } elseif ($objects[$n]['width'] > $m) {
                    $table[$n][$m] = $table[$n - 1][$m];
                } else {
                    $table[$n][$m] = $table[$n - 1][$m] > $table[$n - 1][$m - ($objects[$n]['width'])] + $objects[$n]['value'] ? $table[$n - 1][$m] : $table[$n - 1][($m - $objects[$n]['width'])] + $objects[$n]['value'];
                }
            }
        }

        //-----check and pull out the best object can placement in backpack-----//
        $result = $table[$objectsNumber][$capecity];
        while ($result > 0) {
            if ($table[$objectsNumber][$capecity] == $table[$objectsNumber - 1][$capecity]) {
                $objectsNumber -= 1;
                $result = $table[$objectsNumber][$capecity];
            } else {
                array_push($results, $objects[$objectsNumber]);
                $capecity -= $objects[$objectsNumber]['width'];
                $objectsNumber -= 1;
                $result = $table[$objectsNumber][$capecity];
            }
        }

        //----set session best placement of backpack in {results}
        $_SESSION['results'] = $results;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>knapstack (1/0) الگوریتم کوله پشتی</title>
    <link href="./assets/bootstarp-4/css/bootstrap.css" rel="stylesheet">
</head>
<body>
<div class="col-md-11 border border-secondary border-5 ml-3 mt-3">
    <?php
        if (isset($_SESSION['Errors'])) {
            foreach ($_SESSION['Errors'] as $error) {
                echo "<p class='badge badge-warning'>$error</p></br>";
            }
            $_SESSION['Errors'] = [];
        }
    ?>
    <h1 class="text-primary mt-3 ml-3">الگوریتم کوله پشتی (knapstack) :</h1>
    <hr>
    <div class="d-flex justify-content-center">
        <form class="col-md-12 d-flex flex-row justify-content-between mb-2" action="index.php" method="post">
            <div class="col-md-5">
                <label for="">حجم کوله :</label>
                <input type="number" name="capecity" class="form-control"
                       placeholder="حجم کوله را وارد کنید (کیلوگرم-KG)">
            </div>
            <div class="col-md-6">
                <div class="col-md-12 " id="ObjectsDiv">

                </div>
                <button id="Add-ObjectDiv-Button" type="button" class="btn btn-sm btn-warning"> افزودن شی</button>
            </div>
            <button class="btn btn-success my-3" type="submit">محاسبه</button>
        </form>

    </div>
</div>
<div class="d-flex flex-row justify-content-center align-items-center mt-5">
    <div class="col-md-6 border border-success font-weight-bold text-center">
        <h3>با ارزش ترین چینش کوله</h3>
        <br>
        <!--    show best information for backpack   -->
            <?php
                if(isset($_SESSION['results'])){
                     foreach ($_SESSION['results'] as $result){
                         echo "<h4 dir='rtl'>شی {$result['name']} با وزن {$result['width']} کیلوگرم و ارزش {$result['value']} </h4>","<br>";
                     }
                     $_SESSION['results']=[];
                }
            ?>
        <!--      end section      -->
    </div>
</div>

</body>
<script src="./assets/jquery-3.6.0.min.js"></script>
<script src="./assets/bootstarp/js/bootstrap.bundle.js"></script>
<script src="./assets/jquery-3.6.0.min.js"></script>
<script>
    let myApp = {
        ObjectsDivChildern: 1
    }
    $(`#Add-ObjectDiv-Button`).click(function () {
        $(`#ObjectsDiv`).append(function () {
            return `
                        <div class="d-flex flex-row">
<div class="text-center text-primary">${myApp.ObjectsDivChildern} :</div>
                            <div class="form-group mx-1">
                                <label for="">نام شی :</label>
                                <input type="text" name="object[${myApp.ObjectsDivChildern}][name]" class="form-control">
                            </div>
                            <div class="form-group mx-1">
                                <label for="">وزن شی :</label>
                                <input type="number" name="object[${myApp.ObjectsDivChildern}][width]" class="form-control">
                            </div>
                            <div class="form-group mx-1">
                                <label for="">ارزش شی :</label>
                                <input type="number" name="object[${myApp.ObjectsDivChildern}][value]" class="form-control">
                            </div>
                        </div>
            `
        })
        myApp.ObjectsDivChildern += 1
    })
</script>
</html>