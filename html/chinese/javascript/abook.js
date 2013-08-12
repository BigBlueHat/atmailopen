function DelContact(type,email, id) {

	// We are trying to remove a bunch of addresses at the same time
	if(deltotal || type == 'multi')	{

		if (confirm(['你是否確實要刪除' + deltotal + '選定地址簿項目？']))	{
			document.abook.delmulti.value='1';
			document.abook.submit();
			CleanUpAbook();
		}

		return;

	}

	if(type == "group") {
		if (confirm(['你確定要從你的通訊錄刪除用戶組\''+email+'\' ？'])) {
			location.href = "abook.php?func=open&del=1&email=" + escape(email) + "&group=1&id=" + escape(id) + "&current=" + document.abook.current.value;
			CleanUpAbook();

		}
	}
	else {
		if (confirm(['你確定要從你的通訊錄刪除用戶\''+email+'\'？'])) {
			location.href = "abook.php?func=open&del=1&email=" + escape(email) + "&id=" + escape(id) + "&current=" + document.abook.current.value;
			CleanUpAbook();

		}
	}
}

function DelContactShared(type,email, id) {

	// We are trying to remove a bunch of addresses at the same time
	if(deltotal)	{

		if (confirm(['你是否確實要刪除' + deltotal + '選定地址簿項目？']))	{
			document.abook.delmulti.value='1';
			//document.abook.delshared.value='1';

			document.abook.submit();
			CleanUpAbook();
		}

		return;

	}

	if(type == "group") {
		if (confirm(['你確定要從你的通訊錄刪除用戶組\''+email+'\' ？'])) {
			location.href = "abook.php?func=open&abookview=shared&delshared=1&email=" + escape(email) + "&group=1&id=" + escape(id);
			CleanUpAbook();
		}
	}
	else {
		if (confirm(['你確定要從你的通訊錄刪除用戶\''+email+'\'？'])) {
			location.href = "abook.php?func=open&abookview=shared&delshared=1&email=" + escape(email) + "&id=" + escape(id);
			CleanUpAbook();
		}
	}
}

// Toglle the select all/deselect all
var switch_abook = "0";

function switchabook()	{

	if(switch_abook == '0')	{
		allabook();
		switch_abook = '1';
	} else	{
		uncheckallabook();
		switch_abook = '0';
	}

}
function allabook(swch){
	var i=0;
	len=document.abook.elements.length;
	for (i=0;i<len;i++){
		if (document.abook.elements[i].type=='checkbox' && (document.abook.elements[i].name == 'del[]' || document.abook.elements[i].name == 'delgroup[]' || document.abook.elements[i].name == 'delshared[]' || document.abook.elements[i].name == 'delsharedgroup[]')){
			document.abook.elements[i].checked=true;
			TRON(document.abook.elements[i]);
		};
	};
};

function uncheckallabook(swch){
	var i=0;
	len=document.abook.elements.length;
	for (i=0;i<len;i++){
		if (document.abook.elements[i].type=='checkbox' && (document.abook.elements[i].name == 'del[]' || document.abook.elements[i].name == 'delgroup[]' || document.abook.elements[i].name == 'delshared[]' || document.abook.elements[i].name == 'delsharedgroup[]')){
			document.abook.elements[i].checked=false;
			TROFF(document.abook.elements[i]);
		};
	};
};