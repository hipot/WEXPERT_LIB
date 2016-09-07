<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<!--форма заказа тура (всплывающая)-->
<noindex style="display:none;">
    <div class="popup order" id="orderer">
        <div class="c-popup">
            <div class="close">ESC</div>
            <!--close-->
			<?
			global $FORM;            // оглабаливаем $FORM
			$FORM->Start();            // НАЧАЛО формы
			$FORM->ShowErrors();    // ошибки заполнения
			if($FORM->Ready): // если форма готова, т.е. обработана
				?>
                <script type="text/javascript">
					$(function(){
                        var of =  orderForm();
                        of.bind('modality_inited',function(){
                            $('.order_pop_btn').click();
                            setTimeout(function () {
                                $('.popup.order .close').click();
                            }, 4000);
						});
						$('.ready-tours').hide();
					});
                </script>
	         <?endif;?>
                <h2>Заявка на бронирование тура</h2>

	            <div class="ready-tours" <?=($FORM->Ready)?'style="display:block;"':'';?>>Заявка отправлена</div>

                <div class="info-tours">
                    <div class="t-tours">Информация о туре:</div>
                    <!--t-tours-->
                    <div class="cont-tours">
                        <div class="condit">
                            <ul>
                                <li>
	                                <div class="w"><?=$FORM->Label('hotelname')?>:</div>
                                    <span it="v-hotelname">Albatros <a href="#">Apartamentos 3*</a> (Испания, Коста Дорада, Салоу)</span>
									<?=$FORM->Field('hotelname')?>
                                </li>
                                <li>
	                                <div class="w"><?=$FORM->Label('duration')?>:</div>
                                    <span it="v-duration">19.06 — 02.07, 14 ночей</span>
									<?=$FORM->Field('duration')?>
                                </li>
                                <li>
	                                <div class="w"><?=$FORM->Label('tourists')?>:</div>
                                    <span it="v-tourists">2 взрослых, без детей</span>
									<?=$FORM->Field('tourists')?>
                                </li>
                                <li>
	                                <div class="w"><?=$FORM->Label('room')?>:</div>
                                    <span it="v-room">Dbl Standart</span>
									<?=$FORM->Field('room')?>
                                </li>
                                <li>
	                                <div class="w"><?=$FORM->Label('meal')?>:</div>
                                    <span it="v-meal">BB (BED AND BREAKFAST)</span>
									<?=$FORM->Field('meal')?>
                                </li>
                            </ul>
                        </div>
                        <!--condit-->

                        <div class="price-tours">
                            <div class="pr">
	                            <?=$FORM->Label('price')?>:
                                <span it="v-price">68 980 руб</span>
	                            <?=$FORM->Field('price')?>
                            </div>
                            <!--pr-->
                            <p>Данная стоимость является предварительной и может измениться после фактического
                               бронирования</p>
                            <div class="clear"></div>
                        </div>
                        <!--price-tours-->

                        <div class="incl-serv">
                            <div class="t-incl">Включенные услуги</div>
                            <!--t-incl-->
                            <ul>
                                <li>Трансфер Аэропорт — Отель (групповой трансфер)</li>
                                <li>Страховка медицинская</li>
                                <li>Отель <span it="v-hotelname">Albatros Apartamentos 3*</span>, <span it="v-room">Dbl Standard</span>, <span it="v-tourists">2 взрослых</span>, <span it="v-meal">BED AND BREAKFAST</span>,  <span it="v-duration">7 ночей</span>
                                </li>
                                <li>Трансфер Отель — Аэропорт (групповой трансфер)</li>
                                <li>Авиаперелет <span it="v-fromto">Москва — Тенерифе</span></li>
                                <li>Авиаперелет <span it="v-tofrom">Тенерифе — Москва</span></li>
                            </ul>
                        </div>
                        <!--incl-serv-->
                    </div>
                    <!--cont-tours-->
                </div>
            <!--info-tours-->

                <div class="form-tours">
                    <form>
                        <div class="fields">
							<?=$FORM->Label('name')?>
							<?=$FORM->Field('name')?>
                        </div>
                        <!--fields-->

                        <div class="fields">
							<?=$FORM->Label('mail')?>
							<?=$FORM->Field('mail')?>
                        </div>
                        <!--fields-->

                        <div class="fields">
							<?=$FORM->Label('phone_num')?>
                            <div class="fields2">+7 (
								<?=$FORM->Field('phone_code')?>
                                                 )
                            </div>
                            <!--fields2-->
                            <div class="fields3">
								<?=$FORM->Field('phone_num')?>
                            </div>
                            <!--fields3-->
                        </div>
                        <!--fields-->

                        <div class="clear"></div>
                        <div class="clarif">
                            После отправления заявки наш менеджер свяжется с вами в самое ближайшее время!<br>
                            Если вы хотите получить консультацию немедленно, звоните нам по телефону +7 (495) 605-35-06.
                            Спасибо!
                        </div>
                        <!--clarif-->

                        <div class="b-but">
                            <div class="but">Отправить заявку</div>
                        </div>
                    </form>
                </div>
            <!--form-tours-->
				<?
			$scripts = $FORM->End(false); // КОНЕЦ формы
			?>
        </div>
        <!--c-popup-->
    </div>
    <!--popup-->
</noindex>
<?= $scripts?>
