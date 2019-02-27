
const btnMenu = document.querySelector('.btn-menu')
let btnCountClick = 1;
btnMenu.addEventListener('click', function() {
  // let teste = (btnCountClick % 2) == 0;
  btnMenu.classList.toggle('ativo')
  document.querySelector('.menu-principal').classList.toggle('ativo')
/*
  if((btnCountClick % 2) == 0){
    btnMenu.querySelector('img:nth-of-type(1)').classList.toggle('ativo');
    btnMenu.querySelector('img:nth-of-type(2)').classList.toggle('ativo');
  }else{
 
    btnMenu.querySelector('img:nth-of-type(2)').classList.toggle('ativo');
    btnMenu.querySelector('img:nth-of-type(1)').classList.toggle('ativo');
  }

  btnCountClick++;
  */
})
/*


btnMenu.querySelector('img:nth-of-type(2)')


*/