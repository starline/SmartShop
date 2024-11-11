{include file='settings/theme_menu_part.tpl'}

{if $theme->name}
	{$meta_title = "Тема {$theme->name}" scope=global}
{/if}

{if $message_error}
	<!-- Системное сообщение -->
	<div class="message message_error">
		<span class="text">{if $message_error == 'permissions'}Установите права на запись для папки {$themes_dir}
			{elseif $message_error == 'name_exists'}Тема с таким именем уже существует
			{else}{$message_error}
			{/if}</span>
	</div>
{/if}

<div class="header_top">
	<h1 class="{if $theme->locked}locked{/if}">Текущая тема &mdash; {$theme->name}</h1>
	<a class="add" href="#">Создать копию темы {$settings->theme}</a>
</div>

<form method="post" enctype="multipart/form-data">
	<input type=hidden name="session_id" value="{$smarty.session.id}">
	<input type=hidden name="action">
	<input type=hidden name="theme">

	<div class="columns">
		<div class="block_flex w100 layer">
			<ul class="themes">
				{foreach $themes as $t}
					<li theme="{$t->name|escape}">
						<div class="head_wrap">
							{if $theme->name == $t->name}
								<img class="tick" src='/{$config->templates_subdir}images/tick.png'>
							{/if}

							{if $t->locked}
								<img class="tick" src='/{$config->templates_subdir}images/lock_small.png'>
							{/if}

							{if $theme->name != $t->name && !$t->locked}
								<a href='#' title="Удалить" class='delete'>
									<img src='/{$config->templates_subdir}images/delete.png'>
								</a>
								<a href='#' title="Переименовать" class='edit'>
									<img src='/{$config->templates_subdir}images/pencil.png'>
								</a>
							{elseif !$t->locked}
								<a href='#' title="Удалить" class='delete'>
									<img src='/{$config->templates_subdir}images/delete.png'>
								</a>
								<a href='#' title="Изменить название" class='edit'>
									<img src='/{$config->templates_subdir}images/pencil.png'>
								</a>
							{/if}

							{if $theme->name == $t->name}
								<p class="name">{$t->name|escape|truncate:16:'...'}</p>
							{else}
								<p class="name">
									<a href='#' class='set_main_theme'>{$t->name|escape|truncate:16:'...'}</a>
								</p>
							{/if}
						</div>
						
						<img class="preview" src='/templates/{$t->name}/preview.png'>
					</li>
				{/foreach}
			</ul>
		</div>

		<div class="block_flex w100 btn_row">
			<input class="button_green" type="submit" name="save" value="Сохранить" />
		</div>
	</div>
</form>


<script>
	{literal}

		$(function() {

			// Выбрать тему
			$('.set_main_theme').click(function() {
				$("form input[name=action]").val('set_main_theme');
				$("form input[name=theme]").val($(this).closest('li').attr('theme'));
				$("form").submit();
			});

			// Клонировать текущую тему
			$('.header_top .add').click(function() {
				$("form input[name=action]").val('clone_theme');
				$("form").submit();
			});

			// Редактировать название
			$("a.edit").click(function() {
				name = $(this).closest('li').attr('theme');
				inp1 = $('<input type=hidden name="old_name[]">').val(name);
				inp2 = $('<input type=text name="new_name[]">').val(name);
				$(this).closest('li').find("p.name").html('').append(inp1).append(inp2);
				inp2.focus().select();
				return false;
			});

			// Удалить тему
			$('.delete').click(function() {
				$("form input[name=action]").val('delete_theme');
				$("form input[name=theme]").val($(this).closest('li').attr('theme'));
				$("form").submit();
			});

			$("form").submit(function() {
				if ($("form input[name=action]").val() == 'delete_theme' && !confirm('Подтвердите удаление'))
					return false;
			});

		});
	{/literal}
</script>