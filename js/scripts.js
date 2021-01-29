$( document ).ready(function() {
    $('.selectpicker').selectpicker()
    $('.telefone').mask('(00) 00000-0000')
    $('.cep').mask('00000-000')
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