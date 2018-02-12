function ValueGroup(name){
	var obj;
	obj=document.getElementsByName(name);
	if(obj!=null){
		var i;
		for(i=0;i<obj.length;i++){
			if(obj[i].checked){
				return obj[i].value;
			}
		}
	}
	return null;
}
function character(num){
	if(ValueGroup('zifu1_5')==0){
		$('#zifu1_1,#zifu1_2,#zifu1_3,#zifu1_4').attr('disabled',true);
		$('.label').css('color','#999');
		$('#text1').attr('readonly',false);
		var t=$('#text1').val();
		$('#text1').val('').focus().val(t);
		$('#text1').css('border-color','#59f');
	}else{
		$('#zifu1_1,#zifu1_2,#zifu1_3,#zifu1_4').attr('disabled',false);
		$('.label').css('color','#333');
		$('#text1').attr('readonly',true);
		$('#text1').blur();
		$('#text1').css('border-color','#ccc');
	}
	var c='', s;
	for(var i=1;i<5;i++){
		s=ValueGroup('zifu'+num+'_'+i);
		if(s)c+=s;
	}
	$('#text'+num).val(c);
}
function getRanPass(){
	var n=$('#num').val(), t=$('#text1').val(), o="", p=0;
	if(n>99)return alert('密码位数最多99位！');
	if(n<4)return alert('密码位数最少4位！');
	for(var i=0;i<n;i++){
		p=Math.floor(Math.random()*99999)%t.length;
		o+=t.substr(p,1);
	}
	if(ValueGroup('zifu1_5')!=0){
		if(ValueGroup('zifu1_1')) if(!/[0-9]/.test(o)) return getRanPass();
		if(ValueGroup('zifu1_2')) if(!/[a-z]/.test(o)) return getRanPass();
		if(ValueGroup('zifu1_3')) if(!/[A-Z]/.test(o)) return getRanPass();
		if(ValueGroup('zifu1_4')) if(!/[^0-9a-zA-Z]/.test(o)) return getRanPass();
	}
	$('#mm').html(o.replace(/</g,"&lt;"));
	if(o!='')
	$('#btn1').css('display','block');
	else
	$('#btn1').css('display','none');
}
function getkey(e){var keynum;if(window.event)keynum=e.keyCode;else if(e.which)keynum=e.which;if(keynum==13)getRanPass()}
function clipboard(element,command,info) {
	var text = document.getElementById(element);
	if (text.innerHTML=='') return false;
	var error = '浏览器不支持，请手动操作。';
	if (document.body.createTextRange) {
		var range = document.body.createTextRange();
		range.moveToElementText(text);
		range.select();
	} else if (window.getSelection) {
		var selection = window.getSelection();
		var range = document.createRange();
		range.selectNodeContents(text);
		selection.removeAllRanges();
		selection.addRange(range);
	} else {
		return alert(error);
	}
	try {
		if(document.execCommand(command, false, null)){
			if(element=='mm')
			$('#info1').html(info);
			else
			$('#info2').html(info);
		} else{
			alert(error);
		}
	} catch(err) {
		alert(error);
	}
}
document.onclick=function(e) {
	var target = e.target.id;
	if(target!='btn1')
	$('#info1').html('');
	if(target!='btn2')
	$('#info2').html('');
}
