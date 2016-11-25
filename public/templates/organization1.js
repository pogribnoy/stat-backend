<div class="container-fluid">
	<div class="row">
		<div class="pull-right">
			{{if descriptor.operations ~descriptor=descriptor}}
				{{for descriptor.operations tmpl="operation" /}}
			{{/if}}
		</div><!-- /.pull-right -->
	</div><!-- /.row -->
	
	<div class="row-fluid">
		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class=""><a href="#entity_tab_main" aria-controls="entity_tab_main" role="tab" data-toggle="tab">Основная информация</a></li>
			
			{{if descriptor.scrollers && descriptor.scrollers.expenselist}}<li role="presentation" class=""><a href="#entity_tab_expenses" aria-controls="entity_tab_expenses" role="tab" data-toggle="tab">Расходы</a></li>{{/if}}
			{{if descriptor.scrollers && descriptor.scrollers.messages}}<li role="presentation" class=""><a href="#entity_tab_messages" aria-controls="entity_tab_messages" role="tab" data-toggle="tab">Сообщения</a></li>{{/if}}
			{{if descriptor.scrollers && descriptor.scrollers.userlist}}<li role="presentation" class="active"><a href="#entity_tab_userlist" aria-controls="entity_tab_userlist" role="tab" data-toggle="tab">Пользователи</a></li>{{/if}}
		</ul>	
		<!-- Tab panes -->
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane" id="entity_tab_main">
				<div class="container-fluid">
					<div class="row-fluid">&nbsp;</div>
					<div class="form-horizontal">
						{{if descriptor.fields ~fields=~utilities.objectToArray(descriptor.fields)}}
							{{for ~fields}}
								<div class="form-group" name="field_{{:id}}">
									<label for="field_{{:id}}_value" class="col-sm-2 control-label">{{:name}}</label>
									<div class="col-sm-8">
										{{if type == "label"}}
											<p id="field_{{:id}}_value" class="form-control-static">{{:value}}</p>
										{{else type == "text"}}
											<input type="text" class="form-control" id="field_{{:id}}_value" placeholder="{{:name}}" value="{{:value}}">
										{{else type == "select"}}
											<select id="field_{{:id}}_value" class="form-control" style="width:auto;">
												{{if (nullable && nullable==1) }}<option value="*" {{if value_id==''}}selected="selected"{{/if}}></option>{{/if}}
												{{for values ~value_id=value_id}}
													<option value="{{:id}}" {{if id==~value_id}}selected="selected"{{/if}}>{{:name}}</option>
												{{/for}}
											</select>
										{{else type == "email"}}
											<input type="email" class="form-control" id="field_{{:id}}_value" placeholder="Email">
										{{/if}}
									</div>
								</div>
							{{/for}}
						{{/if}}
					</div>
				</div>
			</div>
			{{if descriptor.scrollers && descriptor.scrollers.expenselist}}
				<div role="tabpanel" class="tab-pane" id="entity_tab_expenses">
					<div class="container-fluid">
						<div class="row-fluid">&nbsp;</div>
						<div class="form-horizontal">
							<div name="placeholder_{{:descriptor.scrollers.expenselist.local_data.container_id}}"></div>
						</div>
					</div>
				</div>
			{{/if}}
			{{if descriptor.scrollers && descriptor.scrollers.messages}}
				<div role="tabpanel" class="tab-pane" id="entity_tab_messages">
					<div class="container-fluid">
						<div class="row-fluid">&nbsp;</div>
						<div class="form-horizontal">
							<div name="placeholder_{{:descriptor.scrollers.messages.local_data.container_id}}"></div>
						</div>
					</div>
				</div>
			{{/if}}
			{{if descriptor.scrollers && descriptor.scrollers.userlist}}
				<div role="tabpanel" class="tab-pane active" id="entity_tab_userlist">
					<div class="container-fluid">
						<div class="row-fluid">&nbsp;</div>
						<div class="form-horizontal">
							<div name="placeholder_{{:descriptor.scrollers.userlist.local_data.container_id}}"></div>
						</div>
					</div>
				</div>
			{{/if}}
		</div>
	</div><!-- /.row-fluid -->
</div><!-- /.container-fluid -->