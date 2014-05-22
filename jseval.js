function todo1() {
  //......todo
  console.log(1);
  return eval;
}
function todo2() {
 console.log(2);
  return function() {}
}
function todo3(){
  console.log(3)
  return new Function();
}
function todoN() {
  console.log('顺序执行末尾')
}

todo1()(todo3())(todo2())(todoN())
