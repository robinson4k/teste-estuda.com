const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
})

$( document ).ready(function() {
    $('.selectpicker').selectpicker()

    // MASCARA
    $('.telefone').mask('(00) 00000-0000')
    $('.cep').mask('00000-000')


    // MODULO DE ESCOLA - PESQUISAR CEP
    $('#cep').keyup(function() {
        let cep = $(this).val()
        if (cep.length == 9) {
            axios.get('https://estuda.com/apps/api/cep', {
                params: {
                    q: cep.replace('-', '')
                }
            })
            .then(function(response) {
                if (response.data.total > 3) {
                    $('#endereco').val(`${response.data.cep.uf}/${response.data.cep.cidade} - ${response.data.cep.bairro}, ${response.data.cep.logradouro}`)
                    Toast.fire({
                        icon: 'success',
                        title: `CEP ${cep} encontrado`
                    })
                } else
                    Toast.fire({
                        icon: 'warning',
                        title: 'CEP não encontrado'
                    })
            })
        }
    })


    // MODULO DE ESCOLAS - PESQUISA NOME DA ESCOLA
    $('#escola_nome').keyup(function() {
        if ($(this).val().length > 2) {
            $('#resultSearch').html('')
            axios.get('https://estuda.com/apps/api/variaveis_escolas', {
                params: {
                    q: $(this).val(),
                    uf: $('#estado').val()
                }
            })
            .then((response) => {
                Object.entries(response.data.variaveis_escolas).forEach(([key, value]) => {
                    $('<a />', {
                        text: value.nome,
                        href: "javascript:void(0)",
                        click: function() {
                            $('#escola_nome').val(value.nome)
                            $('#resultSearch').html('').hide()
                        }
                    }).wrap('<li />').parent().appendTo('#resultSearch');
                });
                $('#resultSearch').show()
            })
            .catch((error) => {
                console.log(error.response)
            })
        }
    }).focusout(function() {
        setTimeout(function() {
            $('#resultSearch' + $(this).data('hash')).css('display', 'none')
        }, 10000)
    })
})


function excluir(url, url_redirect = false) {
    Swal.fire({
        title: 'Deseja realmente excluir este registro?',
        text: "Você não poderá reverter isso!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'SIM',
        cancelButtonText: 'CANCELAR'
    }).then((result) => {
        if (result.value) {
            axios.get(url)
            .then((response) => {
                if (response.data.success) {
                    if (url_redirect)
                        window.location.replace(url_redirect)
                    else
                        location.reload()
                } else if (response.data.error)
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: response.data.error,
                    })
            })
            .catch((error) => {
                console.log(error.response)
            })
        }
    })
}