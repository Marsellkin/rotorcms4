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

if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}
if (isset($_GET['act'])) {
	$act = check($_GET['act']);
} else {
	$act = 'index';
}

show_title('Контакт-лист');

if (is_user()) {
	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			$total = DB::run() -> querySingle("SELECT count(*) FROM `contact` WHERE `contact_user`=?;", array($log));

			if ($total > 0) {
				if ($start >= $total) {
					$start = last_page($total, $config['contactlist']);
				}

				$querycontact = DB::run() -> query("SELECT * FROM `contact` WHERE `contact_user`=? ORDER BY `contact_time` DESC LIMIT ".$start.", ".$config['contactlist'].";", array($log));

				echo '<form action="contact.php?act=del&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'" method="post">';

				while ($data = $querycontact -> fetch()) {
					echo '<div class="b">';
					echo '<div class="img">'.user_avatars($data['contact_name']).'</div>';

					echo '<b>'.profile($data['contact_name']).'</b> <small>('.date_fixed($data['contact_time']).')</small><br />';
					echo user_title($data['contact_name']).' '.user_online($data['contact_name']).'</div>';

					echo '<div>';
					if (!empty($data['contact_text'])) {
						echo 'Заметка: '.$data['contact_text'].'<br />';
					}

					echo '<input type="checkbox" name="del[]" value="'.$data['contact_id'].'" /> ';
					echo '<a href="/pages/private.php?act=submit&amp;uz='.$data['contact_name'].'">Написать</a> | ';
					echo '<a href="/pages/perevod.php?uz='.$data['contact_name'].'">Перевод</a> | ';
					echo '<a href="/pages/contact.php?act=note&amp;id='.$data['contact_id'].'">Заметка</a>';
					echo '</div>';
				}

				echo '<br /><input type="submit" value="Удалить выбранное" /></form>';

				page_strnavigation('contact.php?', $config['contactlist'], $start, $total);

				echo 'Всего в контактах: <b>'.(int)$total.'</b><br />';
			} else {
				show_error('Контакт-лист пуст!');
			}

			echo '<br /><div class="form"><form method="post" action="contact.php?act=add&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">';
			echo '<b>Логин юзера:</b><br /><input name="uz" />';
			echo '<input value="Добавить" type="submit" /></form></div><br />';
		break;

		############################################################################################
		##                                 Добавление пользователей                               ##
		############################################################################################
		case 'add':

			$uid = check($_GET['uid']);
			if (isset($_POST['uz'])) {
				$uz = check($_POST['uz']);
			} elseif (isset($_GET['uz'])) {
				$uz = check($_GET['uz']);
			} else {
				$uz = "";
			}

			if ($uid == $_SESSION['token']) {
				if ($uz != $log) {
					$queryuser = DB::run() -> querySingle("SELECT `users_id` FROM `users` WHERE `users_login`=? LIMIT 1;", array($uz));
					if (!empty($queryuser)) {

						$total = DB::run() -> querySingle("SELECT count(*) FROM `contact` WHERE `contact_user`=?;", array($log));
						if ($total <= $config['limitcontact']) {
							// ------------------------ Проверка на существование ------------------------//
							if (!is_contact($log, $uz)){

								DB::run() -> query("INSERT INTO `contact` (`contact_user`, `contact_name`, `contact_time`) VALUES (?, ?, ?);", array($log, $uz, SITETIME));
								// ----------------------------- Проверка на игнор ----------------------------//
								$ignorstr = DB::run() -> querySingle("SELECT `ignore_id` FROM `ignore` WHERE `ignore_user`=? AND `ignore_name`=? LIMIT 1;", array($uz, $log));
								if (empty($ignorstr)) {
									DB::run() -> query("UPDATE `users` SET `users_newprivat`=`users_newprivat`+1 WHERE `users_login`=?", array($uz));
									// ------------------------------Уведомление по привату------------------------//
									$textpriv = '<img src="/images/img/custom.gif" alt="custom" /> Пользователь [b]'.nickname($log).'[/b] добавил вас в свой контакт-лист!';
									DB::run() -> query("INSERT INTO `inbox` (`inbox_user`, `inbox_author`, `inbox_text`, `inbox_time`) VALUES (?, ?, ?, ?);", array($uz, $log, $textpriv, SITETIME));
								}

								$_SESSION['note'] = 'Пользователь успешно добавлен в контакты!';
								redirect("contact.php?start=$start");

							} else {
								show_error('Ошибка! Данный пользователь уже есть в контакт-листе!');
							}
						} else {
							show_error('Ошибка! В контакт-листе разрешено не более '.$config['limitcontact'].' пользователей!');
						}
					} else {
						show_error('Ошибка! Данного адресата не существует!');
					}
				} else {
					show_error('Ошибка! Запрещено добавлять свой логин!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="contact.php?start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                    Изменение заметки                                   ##
		############################################################################################
		case 'note':

			if (isset($_GET['id'])) {
				$id = abs(intval($_GET['id']));
			} else {
				$id = 0;
			}

			if ($id > 0) {
				$data = DB::run() -> queryFetch("SELECT * FROM contact WHERE contact_id=? AND contact_user=? LIMIT 1;", array($id, $log));

				if (!empty($data)) {
					echo '<img src="/images/img/edit.gif" alt="image" /> Заметка для пользователя <b>'.nickname($data['contact_name']).'</b> '.user_online($data['contact_name']).':<br /><br />';

					echo '<div class="form">';
					echo '<form method="post" action="contact.php?act=editnote&amp;id='.$id.'&amp;start='.$start.'&amp;uid='.$_SESSION['token'].'">';
					echo 'Заметка:<br />';
					echo '<textarea cols="25" rows="5" name="msg">'.$data['contact_text'].'</textarea><br />';
					echo '<input value="Редактировать" name="do" type="submit" /></form></div><br />';
				} else {
					show_error('Ошибка редактирования заметки!');
				}
			} else {
				show_error('Ошибка! Не выбран пользователь для добавления заметки!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="contact.php?start='.$start.'">Вернуться</a><br />';
		break;

		############################################################################################
		##                                   Добавление заметки                                   ##
		############################################################################################
		case 'editnote':
			$uid = check($_GET['uid']);
			$msg = check($_POST['msg']);
			if (isset($_GET['id'])) {
				$id = abs(intval($_GET['id']));
			} else {
				$id = 0;
			}

			if ($uid == $_SESSION['token']) {
				if ($id > 0) {
					if (utf_strlen($msg) < 1000) {
						DB::run() -> query("UPDATE contact SET contact_text=? WHERE contact_id=? AND contact_user=?;", array($msg, $id, $log));

						$_SESSION['note'] = 'Заметка успешно отредактирована!';
						redirect("contact.php?start=$start");

					} else {
						show_error('Ошибка! Слишком длинная заметка (не более 1000 символов)!');
					}
				} else {
					show_error('Ошибка! Не выбран пользователь для добавления заметки!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="contact.php?act=note&amp;id='.$id.'&amp;start='.$start.'">Вернуться</a><br />';
			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="contact.php?start='.$start.'">К спискам</a><br />';
		break;

		############################################################################################
		##                                   Удаление пользователей                               ##
		############################################################################################
		case 'del':

			$uid = check($_GET['uid']);
			if (isset($_POST['del'])) {
				$del = intar($_POST['del']);
			} else {
				$del = 0;
			}

			if ($uid == $_SESSION['token']) {
				if ($del > 0) {
					$del = implode(',', $del);
					DB::run() -> query("DELETE FROM contact WHERE contact_id IN (".$del.") AND contact_user=?;", array($log));

					$_SESSION['note'] = 'Выбранные пользователи успешно удалены из контактов!';
					redirect("contact.php?start=$start");

				} else {
					show_error('Ошибка! Не выбраны пользователи для удаления!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="contact.php?start='.$start.'">Вернуться</a><br />';
		break;

	default:
		redirect("contact.php");
	endswitch;

} else {
	show_login('Вы не авторизованы, для просмотра контакт-листа, необходимо');
}

echo '<img src="/images/img/ignor.gif" alt="image" /> <a href="ignore.php">Игнор-лист</a><br />';
echo '<img src="/images/img/mail.gif" alt="image" /> <a href="private.php">Сообщения</a><br />';

include_once ('../themes/footer.php');
?>
