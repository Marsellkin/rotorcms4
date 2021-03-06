<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
require_once ('../includes/start.php');
require_once ('../includes/functions.php');
require_once ('../includes/header.php');
include_once ('../themes/header.php');

if (function_exists('set_time_limit')) {
	set_time_limit(600);
}

if (isset($_GET['act'])) {
	$act = check($_GET['act']);
} else {
	$act = 'index';
}

if (is_admin(array(101))) {
	show_title('Backup базы данных');

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			$globfiles = glob(DATADIR."/backup/*.{zip,gz,bz2,sql}", GLOB_BRACE);
			$total = count($globfiles);

			if (is_array($globfiles) && $total > 0) {
				arsort($globfiles);

				foreach($globfiles as $value) {
					echo '<img src="/images/img/zip.gif" alt="image" /> <b>'.basename($value).'</b> ('.read_file($value).') (<a href="backup.php?act=del&amp;backup='.basename($value).'&amp;uid='.$_SESSION['token'].'">Удалить</a>)<br />';
				}

				echo '<br />Всего бэкапов: <b>'.$total.'</b><br /><br />';
			} else {
				show_error('Бэкапов еще нет!');
			}

			echo '<img src="/images/img/open.gif" alt="image" /> <a href="backup.php?act=choice">Новый бэкап</a><br />';
		break;

		############################################################################################
		##                                    Выбор таблиц                                        ##
		############################################################################################
		case 'choice':
			$q = DB::run() -> query("SHOW TABLE STATUS;");
			$tables = $q -> fetchAll();

			$total = count($tables);

			if ($total > 0) {
				echo 'Всего таблиц: <b>'.$total.'</b><br /><br />';

				echo '<div class="form">';
				echo '<form action="backup.php?act=backup&amp;uid='.$_SESSION['token'].'" method="post">';

				echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked" /> <b><label for="all">Отметить все</label></b><hr />';

				foreach ($tables as $data) {
					echo '<input type="checkbox" name="tables[]" value="'.$data['Name'].'" /> ';
					echo '<img src="/images/img/database.gif" alt="db" /> <b>'.$data['Name'].'</b> (Записей: '.$data['Rows'].' / Размер: '.formatsize($data['Data_length']).')<br />';
				}

				echo '<br />Метод сжатия:<br />';
				echo '<select name="method">';

				echo '<option value="0">Не сжимать</option>';

				if (function_exists("gzopen")) {
					echo '<option value="1" selected="selected">GZip</option>';
				}

				if (function_exists("bzopen")) {
					echo '<option value="2">BZip2</option>';
				}

				echo '</select><br />';

				$level = array(0 => 'Без сжатия', 1 => '1 (минимальная)', 2 => '2', 3 => '3', 4 => '4', 5 => '5 (средняя)', 6 => '6', 7 => '7 (рекомендуемая)', 8 => '8', 9 => '9 (максимальная)');

				echo 'Степень сжатия:<br />';
				echo '<select name="level">';
				foreach($level as $key => $val) {
					$selected = ($key == 7) ? ' selected="selected"' : '';
					echo '<option value="'.$key.'"'.$selected.'>'.$val.'</option>';
				}
				echo '</select><br />';

				echo '<br /><input type="submit" value="Выполнить" /></form></div><br />';
			} else {
				show_error('Нет таблиц для бэкапа!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="backup.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                        Бэкап                                           ##
		############################################################################################
		case 'backup':

			define('BACKUP', date('d-M-Y_H-i-s', SITETIME));

			if (!empty($_POST['tables'])) {
				$tables = check($_POST['tables']);
			} else {
				$tables = 0;
			}
			$method = abs(intval($_POST['method']));
			$level = abs(intval($_POST['level']));

			if (is_writeable(DATADIR.'/backup')) {
				if (!empty($tables)) {
					$q = DB::run() -> query("SHOW TABLE STATUS;");
					$chktbl = $q -> fetchAll(PDO::FETCH_COLUMN);

					$diff = array_diff($tables, $chktbl);
					if (empty($diff)) {
						$file = 'backup_'.BACKUP.'.sql';

						$fp = fn_open(DATADIR.'/backup/'.$file, "w", $method, $level);

						foreach ($tables as $data) {
							DB::run() -> query("LOCK TABLES `{$data}` WRITE;");
							$result = DB::run() -> query("SHOW CREATE TABLE `{$data}`;");
							$tab = $result -> fetch(PDO::FETCH_NUM);
							// $tab = preg_replace('/(default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP|DEFAULT CHARSET=\w+|character set \w+|collate \w+)/i', '/*!40101 \\1 */', $tab);
							fn_write($fp, "--\n-- Структура таблицы `{$data}`\n--\n\n", $method);
							fn_write($fp, "DROP TABLE IF EXISTS `{$data}`;\n{$tab[1]};\n\n", $method);

							$total = DB::run() -> querySingle("SELECT count(*) FROM `{$data}`;");

							if ($total > 0) {

								$NumericColumn = array();
								$result = DB::run() -> query("SHOW COLUMNS FROM `{$data}`");
								$field = 0;
								while ($numcol = $result -> fetch(PDO::FETCH_NUM)) {
									$NumericColumn[$field++] = preg_match("/^(\w*int|year)/", $numcol[1]) ? 1 : 0;
								}
								$fields = $field;

								fn_write($fp, "--\n-- Дамп данных таблицы `{$data}`\n--\n\n", $method);
								fn_write($fp, "INSERT INTO `{$data}` VALUES ", $method);

								$col = DB::run() -> query("SELECT * FROM `{$data}`;");

								$num = 0;
								while ($row = $col -> fetch(PDO::FETCH_NUM)) {
									$num++;
									for($k = 0; $k < $fields; $k++) {
										if ($NumericColumn[$k]) {
											$row[$k] = isset($row[$k]) ? $row[$k] : "NULL";
										} else {
											$row[$k] = isset($row[$k]) ? "'".$row[$k]."'" : "NULL";
										}
									}
									fn_write($fp, ($num == 1 ? '' : ',')."\n(".implode(', ', $row).")", $method);
								}

								fn_write($fp, ";\n\n", $method);
							}
						}

						DB::run() -> query("UNLOCK TABLES;");

						fn_close($fp, $method);

						$_SESSION['note'] = 'База данных успешно обработана и сохранена!';
						redirect("backup.php");

					} else {
						show_error('Ошибка! Некоторые таблицы отсутствуют в базе данных!');
					}
				} else {
					show_error('Ошибка! Не выбраны таблицы для бэкапа!');
				}
			} else {
				show_error('Ошибка! Директория backup недоступна для записи!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="backup.php?act=choice">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Удаление бэкапов                                    ##
		############################################################################################
		case 'del':

			$uid = check($_GET['uid']);
			$backup = check($_GET['backup']);

			if ($uid == $_SESSION['token']) {
				if (!empty($backup)) {
					if (preg_match('|^[\w\.\-]+$|i', $backup)) {
						if (file_exists(DATADIR.'/backup/'.$backup)) {
							unlink (DATADIR.'/backup/'.$backup);

							$_SESSION['note'] = 'Бэкап успешно удален!';
							redirect("backup.php");

						} else {
							show_error('Ошибка! Данного бэкапа не существует!');
						}
					} else {
						show_error('Ошибка! Недопустимое название бэкапа!');
					}
				} else {
					show_error('Ошибка! Вы не выбрали бэкапа для удаления!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="backup.php">Вернуться</a><br />';
		break;

	default:
		redirect("backup.php");
	endswitch;

	echo '<img src="/images/img/panel.gif" alt="image" /> <a href="index.php">В админку</a><br />';

} else {
	redirect('/index.php');
}

include_once ('../themes/footer.php');
?>
