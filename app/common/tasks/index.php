<?php foreach($tasks as $code => $task) {?>
	<div class="panel panel-info">
		<div class="panel-heading"><?php echo $task["name"]; ?></div>
		<div class="panel-body">
			<div id="<?php echo $code; ?>TaskFrom" class="form-horizontal">
				<div class="form-group" name="field_schedule">
					<label for="field_schedule_value" class="col-sm-2 col-md-2 col-lg-1 control-label">Расписание</label>
					<div class="col-sm-10 col-md-10 col-lg-11">
						<input type="text" class="form-control" id="field_schedule_value" placeholder="Расписание" value="<?php echo $task["schedule"]; ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
<?php } ?>	
<div class="form-group">
	<button type="button" class="btn btn-primary" aria-label="Сохранить" onclick="saveTasks();">Сохранить</button>
	<button type="button" class="btn btn-default" aria-label="Отмена" onclick="document.reload();">Отмена</button>
</div>


<script type="text/javascript"><!--

function saveTasks() {
	var tasks = {
		clear: {},
	};
	
	// задача Clear
	var fromJQ = $('#clearTaskFrom');
	var schedJQ = from.find('#field_schedule_value');
	tasks.clear.schedule = schedJQ.val(),
	
	$.ajax({
		url: '/tasks/save',
		dataType: 'json',
		method: 'post',
		data: tasks,
		beforeSend: function() {
				// показываем прогрессбар
		},
		complete: function() {
			// скрываем прогрессбар
		},			
		success: function(json) {
			console.log(json);
			handleAjaxSuccess(json.success)
			if(!handleAjaxError(json.error)) {
				//if(json.success.redirect) document.location = json.success.redirect;
			}
			
		},
		error: function(xhr, ajaxOptions, thrownError) {
			console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			handleAjaxError({
				messages: [{
					title: 'Ошибка обмена данными',
					msg: 'Ошибка обработки запроса на стороне сервера. Обратитесь в службу поддержки',
				}],
			});
		}
	});
}


//--></script>