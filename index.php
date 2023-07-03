<?php

require_once 'backend/config.php';
if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

require_once 'backend/sdbh.php';
$dbh = new sdbh();
?>
<html>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="row row-header">
            <div class="col-12">
                <img src="assets/img/logo.png" alt="logo" style="max-height:50px" />
                <h1>Прокат</h1>
            </div>
        </div>
        <div class="row row-body">
            <div class="col-3">
                <span style="text-align: center">Форма расчета:</span>
                <i class="bi bi-activity"></i>
            </div>
            <div class="col-9">
                <form id="form" method="post">
                    <label class="form-label" for="product">Выберите автомобиль:</label>
                    <select class="form-select" name="product" id="product">
                        <?php
                        $products = ($dbh->mselect_rows('a25_products', 1, 0, 3, 'id'));
                        foreach ($products as $product) { ?>
                            <option value="<?= $product['ID'] ?>"><?= $product['NAME'] ?></option>
                        <?php } ?>
                    </select>

                    <label for="days" class="form-label">Количество дней:</label>
                    <input name="days" type="text" class="form-control" id="days" min="1" max="30">
                    <div class="invalid-feedback"></div>

                    <label for="customRange1" class="form-label">Дополнительные услуги:</label>

                    <?php
                    $services = unserialize($dbh->mselect_rows('a25_settings', ['set_key' => 'services'], 0, 1, 'id')[0]['set_value']);
                    $i = 1;
                    foreach ($services as $k => $s) {
                        $i++;
                    ?>
                        <div class="form-check">
                            <input name="services[]" class="form-check-input" type="checkbox" value="<?= $s ?>" id="flexCheckChecked<?= $i ?>">
                            <label class="form-check-label" for="flexCheckChecked<?= $i ?>">
                                <?= $k ?>
                            </label>
                        </div>
                    <?php } ?>
                        <div for="customRange1" id="total-sum" class="form-label"></div>
                    <button type="submit" class="btn btn-primary">Рассчитать</button>
                </form>
                <script type="module">
                    document.getElementById('form').addEventListener('submit', (e) => {
                        e.preventDefault()
                        const productId = document.getElementById('product').value
                        const days = document.getElementById('days').value
                        const checkboxes = document.querySelectorAll('.form-check-input:checked')

                        const services = [];

                        if(checkboxes.length !== 0) {
                            for(let i = 0; i < checkboxes.length; i++) {
                                services.push(checkboxes[i].value)
                            }
                        }

                        fetch('/backend/form.php', {
                                method: 'post',
                                body: JSON.stringify({
                                    productId,
                                    days,
                                    services
                                }),

                                headers: {
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(data => data.json())
                            .then((res) => {
                                if (res.success) {
                                    const totalSumNode = document.getElementById('total-sum');
                                    totalSumNode.innerHTML ='Общая стоимость: ' + res.totalSum;
                                    document.querySelector('.invalid-feedback').style.display='none';
                                } else {
                                    const invalidBlock = document.querySelector('.invalid-feedback');
                                    invalidBlock.innerHTML = res.error;
                                    invalidBlock.style.display='block';
                                }
                            })
                    })
                </script>
            </div>
        </div>
    </div>
</body>

</html>