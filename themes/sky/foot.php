<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
echo '</div></div>
                            <div class="cleared"></div>
                        </div>

                            </div>
                        </div>';

/**
 * echo '                        <div class="art-Post">
 * <div class="art-Post-tl"></div>
 * <div class="art-Post-tr"></div>
 * <div class="art-Post-bl"></div>
 * <div class="art-Post-br"></div>
 * <div class="art-Post-tc"></div>
 * <div class="art-Post-bc"></div>
 * <div class="art-Post-cl"></div>
 * <div class="art-Post-cr"></div>
 * <div class="art-Post-cc"></div>
 * <div class="art-Post-body">
 * <div class="art-Post-inner">
 *
 * <div class="art-PostContent">
 *
 *
 *
 *
 * Содержание блока по центру
 *
 *
 *
 *
 *
 * </div>
 * <div class="cleared"></div>
 * </div>
 *
 * </div>
 * </div>';
 */
echo '</div>';

echo '<div class="art-sidebar2">';

echo '<div class="art-Block">
                            <div class="art-Block-tl"></div>
                            <div class="art-Block-tr"></div>
                            <div class="art-Block-bl"></div>
                            <div class="art-Block-br"></div>
                            <div class="art-Block-tc"></div>
                            <div class="art-Block-bc"></div>
                            <div class="art-Block-cl"></div>
                            <div class="art-Block-cr"></div>
                            <div class="art-Block-cc"></div>
                            <div class="art-Block-body">
                                <div class="art-BlockContent">
                                    <div class="art-BlockContent-body">
                                        <div>';
include (DATADIR.'/main/recent.dat');
echo '</div>
                                    </div>
                                </div>
                            </div>
                        </div>';

echo '</div>';

echo '</div>
                <div class="cleared"></div><div class="art-Footer">
                    <div class="art-Footer-inner">
                        <a href="/news/rss.php" class="art-rss-tag-icon" title="RSS"></a>
                        <div class="art-Footer-text">';
echo '<a href="'.$config['home'].'">'.$config['copy'].'</a><br />';
    show_online();
    show_counter();
    navigation();
echo '</div>';

    perfomance();
echo '</div><div class="art-Footer-background"></div>
                </div>
            </div>
        </div>
    </div>

<div style="text-align:center"><small>
<a href="/pages/faq.php">FAQ (Чаво)</a> |
<a href="/pages/rules.php">Правила</a> |
<a href="/mail/index.php">Поддержка</a>
</small></div>';
echo '</body></html>';
?>
