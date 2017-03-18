# IDPass Introduction

---

## Introduction
It's an online password manager wrote in PHP. Users can store privacy here and the stored privacy is encrypted. Demo website is [IDPass](http://idpass.fairylive.cn). Cantact me if anyone interested in this manager.
  
All privacy is encrypted with AES in browser with private password. Information transfered between browser and server is encrypted with RSA.  
  
It's been designed friendly to users. I have finished the basic functions and I am trying to use this to save my private information.  

By Soe.

## Install
Requirement:PHP 5 and MySQL.  

Clone this repository and edit config.php according to your situation. Then you can managed your privacy via index.php. 

## Usage
Currently only have a Chinese interface, follow below instructions to managed privacy.


# IDPass介绍

---

## 简介
　　这是一个使用PHP编写的在线密码管理工具，用户可以把个人机密信息保存于此，通过使用加密算法可以保证信息的安全存储。
## 如何使用
　　首先你需要注册一个账号。
### 新建记录
　　在主页的*新建记录*可以新建一个记录，在表单中填写记录名称(表名不能为空)，以及相关记录的数据。点击明文/密文切换按钮可以选择是否在数据库中加密存储该项内容，只有明文的数据才能进行[搜索](#search)。点击＋按钮可以添加一项新数据。完成表单填写后，点击*新建*按钮即可创建一个新纪录。
### 记录列表
　　点击主页的*记录列表*以查看已存储的记录。点击记录名称可以查看详细该记录下的各项数据，其中在新建记录时选择为密文的数据将显示其密文形式，要查看明文，使用鼠标点击即可解密并自动复制到粘贴板。使用鼠标点击记录下的数据均可以自动复制到粘贴板。
　　点击记录名称右侧的*删除*，可以删除该记录。点击记录名称右侧的*编辑*，可以编辑该记录。
### 搜索
　　在菜单右侧的搜索框输入要搜索的关键字，关键字不区分大小写，输入完成后按回车即可进行搜索。
### 导出
　　在菜单中点击导出，可以把所有的个人信息导出为一个html文件，单独打开该html文件并输入你的用户名和密码进行解密。
## 注意事项
　　注册账号后，需要注意牢记你的账户名称和密码，所有的加密信息均是以个人的帐密为基础在本地进行AES加密，服务器的数据库中仅仅存储了加密后的个人信息，在无法得到你的帐密信息情况下，即使是身为开发者也无法解密你的信息。
## 如何保证数据安全
　　服务器生成RSA 256bit长度的钥匙，服务器保留私钥，公钥则输出到浏览器页面中。在用户进行登陆时，会在浏览器的sessionStorage记录用户的账户名称和密码的哈希值以作为AES密钥。当用户点击注册或登陆按钮时，账户名称和密码使用公钥进行RSA加密，在服务器中则使用私钥对其进行解密。服务器会生成一个盐值与用户的密码串接，然后使用sha256哈希算法进行加密，结果存储在服务器的数据库中，因此服务器并不存储用户的明文密码。由于sha256哈希算法暂时没有解法，因此若用户忘记密码，所有的加密信息都不可能解密。
　　用户在浏览器在新建或编辑表单数据后，提交表单数据时，表单中需要加密的数据会在浏览器使用在sessionStorage记录的AES密钥进行加密，然后再使用服务器的RSA公钥进行加密，再上传到服务器中。服务器端使用RSA私钥进行解密得到用户填写的表单数据。
　　用户在查询表单数据时，从服务器得到表单数据后，然后在浏览器使用本地存储的AES密钥进行解密。
