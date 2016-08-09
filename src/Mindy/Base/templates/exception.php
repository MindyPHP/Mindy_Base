<!DOCTYPE html>
<!--[if lt IE 7]>
<html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="ru"> <![endif]-->
<!--[if IE 7]>
<html class="no-js lt-ie9 lt-ie8" lang="ru"> <![endif]-->
<!--[if IE 8]>
<html class="no-js lt-ie9" lang="ru"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="ru"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <title><?php echo $data['type']; ?></title>
</head>

<body>
<div class="base-container">
    <div class="message">
        <p><strong>Type: <?php echo $data['type']; ?></strong></p>
        <p><strong>File: <?php echo $data['file']; ?></strong></p>
        <p><strong>Line: <?php echo $data['line']; ?></strong></p>
        <?php echo $data['message']; ?>
    </div>

    <div class="trace-container">
        <div class="source">
            <?php echo $this->renderSource($data['file'], $data['line'], $this->maxSourceLines); ?>
        </div>

        <div class="traces">
            <h2>Stack Trace</h2>
            <table style="width:100%;">
                <?php
                foreach ($data['traces'] as $i => $trace) {
                    $hasCode = $trace['file'] && is_file($trace['file']);
                    ?>
                    <tr class="trace">
                        <td class="content">
                            <div class="trace-file">
                                #<?php echo $i ?> <?php echo $trace['file']; ?>(<?php echo $trace['line']; ?>):

                                <?php if (isset($trace['class'])) { ?>
                                    <strong><?php echo $trace['class'] ?></strong> <?php echo $trace['type'] ?>
                                <?php } ?>

                                <?php if (isset($trace['args'])) { ?>
                                    <strong><?php echo $trace['function'] ?></strong>
                                    <?php echo $this->argsToString($trace['args']); ?>
                                <?php } ?>
                            </div>

                            <?php if ($hasCode) { ?>
                                <?php echo $this->renderSource($trace['file'], $trace['line'], $this->maxSourceLines); ?>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <div class="version">
            <?php echo date('Y-m-d H:i:s', $data['time']); ?><?php echo $data['version']; ?>
        </div>
    </div>
</div>
<style type="text/css">
    <?php
    echo file_get_contents(__DIR__ . '/base.css');
    echo file_get_contents(__DIR__ . '/core.css');
    ?>
</style>
<script type="text/javascript">
    <?php echo file_get_contents(__DIR__ . '/sh.js'); ?>
    SyntaxHighlighter.all();
</script>
</body>
</html>