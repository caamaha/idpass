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
function CharSet(num){
	if(ValueGroup('genpass-set5')==0){
		$('#genpass-set1,#genpass-set2,#genpass-set3,#genpass-set4').attr('disabled',true);
		$('#genpass-set').attr('readonly',false);
		var t=$('#genpass-set').val();
		$('#genpass-set').val('').focus().val(t);
		$('#genpass-set').css('border-color','#59f');
	}else{
		$('#genpass-set1,#genpass-set2,#genpass-set3,#genpass-set4').attr('disabled',false);
		$('#genpass-set').attr('readonly',true);
		$('#genpass-set').blur();
		$('#genpass-set').css('border-color','#ccc');
	}
	var c='', s;
	if(num==1){
		for(var i=1;i<5;i++){
			s=ValueGroup('genpass-set'+i);
			if(s)c+=s;
		}
		$('#genpass-set').val(c);
	}
}
function getRanPass(){
	var n=$('#num').val(), t=$('#genpass-set').val(), o="", p=0;
	if(n>256)return alert('密码位数最多256位！');
	if(n<4)return alert('密码位数最少4位！');
	for(var i=0;i<n;i++){
		p=Math.floor(Math.random()*99999)%t.length;
		o+=t.substr(p,1);
	}
	if(ValueGroup('genpass-set5')!=0){
		if(ValueGroup('genpass-set1')) if(!/[0-9]/.test(o)) return getRanPass();
		if(ValueGroup('genpass-set2')) if(!/[a-z]/.test(o)) return getRanPass();
		if(ValueGroup('genpass-set3')) if(!/[A-Z]/.test(o)) return getRanPass();
		if(ValueGroup('genpass-set4')) if(!/[^0-9a-zA-Z]/.test(o)) return getRanPass();
	}
	$('#text-genpass').html(o.replace(/</g,"&lt;"));
	window._clipboard_text = o;
	$("#cpbtn").click();
}

