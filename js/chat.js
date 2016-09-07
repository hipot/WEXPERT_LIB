// chat
$("#get_chat").click(function(){
	var url = 'http://srv.livecontact.ru/consoles/visitor.php?lkey=4a9635c5012f1' + '&opurl=' + window.document.location.href + '&pagetitle=' + document.title + '';
	var name = 'live_contact';
	var paramsString = 'resizable=yes, toolbar=no ,WIDTH=650 ,HEIGHT=520, location=no, status=yes, scrollbars=yes, menubar=no';

	window.open(url, name, paramsString);
	return false;
});