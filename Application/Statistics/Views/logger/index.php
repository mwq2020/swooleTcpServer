<div class="row clearfix">
    <div class="col-md-12 column">
        <form action="" method="get">
        项目：
        <select name="project_name">
            <option value="0">请选择</option>
            <option value="Club" <?php if(isset($page_request['project_name']) && $page_request['project_name'] == 'Club'){ echo "selected";} ?> >Club</option>
        </select>
        类名：
        <input type="text" name="class_name" value="<?php echo isset($page_request['class_name']) ? $page_request['class_name'] : ''; ?>">

        方法名：
        <input type="text" name="function_name" value="<?php echo isset($page_request['function_name']) ? $page_request['function_name'] : ''; ?>">

        开始时间：
        <input type="text" name="start_time" value="<?php echo isset($page_request['start_time']) ? $page_request['start_time'] : date('Y-m-d 00:00:00'); ?>">

        结束时间：
        <input type="text" name="end_time" value="<?php echo isset($page_request['end_time']) ? $page_request['end_time'] : date('Y-m-d 23:59:59'); ?>">

        <input type="submit" value="提交"/>
        </form>
    </div>
</div>

<?php if(!empty($error_msg)): ?>
<div class="alert alert-dismissable alert-danger">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <strong><?php echo $error_msg;?></strong>
</div>
<?php endif; ?>

<div class="row clearfix">
    <div class="col-md-12 column">
        <?php echo $log_content; ?>
    </div>
</div>