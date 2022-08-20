usersList = [];
document.getElementById('loginForm').classList.remove('d-none')

window.onload = function () {
    document.getElementById('login').addEventListener('click', doLogin)
    document.getElementById('cancelAddUser').addEventListener('click', cancelAddUser)
    document.getElementById('addUser').addEventListener('click', addUser)
    getUsersList()
}

function showAlert(element, text, variant = 'warning', closable=false) {
    let alert = '<div class="alert alert-'+variant+(closable ? ' alert-dismissible fade show': '')+'" role="alert">'
    alert += '<span>'+text+'</span>'
    if (closable) {
        alert += '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
    }
    alert += '</div>'
    document.getElementById(element).innerHTML = alert
    return true
}

function clearAlert(element) {
    document.getElementById(element).innerHTML = '';
    return true;
}

function saveToken(token) {
    localStorage.removeItem("saba_token_key");
    if (typeof token == 'string') {
        localStorage.setItem("saba_token_key", token);
    }
    return true
}

function getToken(token) {
    return localStorage.getItem("saba_token_key");
}

function doLogin() {
    const username = document.getElementById('username').value
    const password = document.getElementById('password').value
    let formData = new FormData();
    formData.append('username', username)
    formData.append('password', password)
    clearAlert('loginFormErrors')
    axios.post('http://localhost/saba/back/users/login.php', formData).then(res => {
        document.getElementById('login').disabled = true
        saveToken(null)
        if (res && res?.data) {
            if (res?.data?.status) {
                saveToken(res.data.result.token)
                getUsersList()
            } else {
                showAlert('loginFormErrors', 'Sign in was not successful...', 'danger', true)
            }
        } else {
            showAlert('loginFormErrors', 'Unknown error while signing in...')
        }
    }).catch(err => {
        saveToken(null)
    }).then(res => {
        document.getElementById('login').disabled = false
        if (typeof getToken() == 'string') {
            document.getElementById('loginForm').classList.add('d-none')
            document.getElementById('usersForm').classList.remove('d-none')
        }
    })
}

function getUsersList() {
    usersList = []
    clearAlert('usersFormErrors')
    axios.post('http://localhost/saba/back/users/list_users.php', {}, {headers: {'Authorization': 'Bearer '+getToken()}}).then(res => {
        if (res && res?.data && res?.data?.status) {
            usersList = res?.data?.result
        } else {
            showAlert('usersFormErrors', 'Error fetching users list', 'danger', true)
        }
    }).catch(err => {
        showAlert('usersFormErrors', 'Error fetching users list', 'danger', true)
    }).then(res => {
        setUsersList()
    })
}

function setUsersList() {
    const active = '  <span class="badge bg-primary ml-3">Active</span>'
    let usersTable = '<table class="table table-sm table-hover table-striped"><thead class="table-light">';
    usersTable +='<tr><th>ID</th><th>Username</th><th>Created</th><th>Actions</th></tr>'
    usersTable +='</thead><tbody></tbody>'
    for (let i = 0; i < usersList.length; i++) {
        let tempDate = new Date(usersList[i]['created_at']).toLocaleString()
        let action = '<div class="btn-group" role="group" aria-label="Actions">'
        action += '  <button type="button" class="btn btn-sm btn-secondary action-btn-edit" data-target="'+usersList[i]['id']+'" id="editPass-'+usersList[i]['id']+'">Change Password</button>'
        action += '  <button type="button" class="btn btn-sm btn-warning action-btn-delete" data-target="'+usersList[i]['id']+'" id="deleteUser-'+usersList[i]['id']+'">Delete</button>'
        action += '</div>'
        usersTable +='<tr><td>'+usersList[i]['id']+'</td><td>'+usersList[i]['username']+(usersList[i]['active'] ? active : '')+'</td><td>'+tempDate+'</td><td>'+action+'</td></tr>'
    }
    usersTable +='</tbody>'

    document.getElementById('userList').innerHTML = usersTable
}

function addUser() {
    const uName = document.getElementById('newUser').value
    const pass  = document.getElementById('newPass').value
    const passC = document.getElementById('newPassConfirm').value
    if (uName && uName.length && pass && pass.length && passC && passC.length && pass === passC) {
        let formData = new FormData();
        formData.append('username', uName)
        formData.append('password', pass)
        clearAlert('usersFormErrors')
        axios.post('http://localhost/saba/back/users/add_user.php', formData, {headers: {'Authorization': 'Bearer '+getToken()}}).then(res => {
            document.getElementById('addUser').disabled = true
            if (res && res?.data) {
                if (res?.data?.status) {
                    getUsersList()
                } else {
                    showAlert('loginFormErrors', 'Can not add user right now', 'danger', true)
                }
            } else {
                showAlert('loginFormErrors', 'Unknown error while creating new user...')
            }
        }).catch(err => {
            showAlert('loginFormErrors', 'Unknown error while creating new user...')
        }).then(res => {
            document.getElementById('addUser').disabled = false
            document.getElementById('cancelAddUser').click()
        })
    } else {
        if (!uName || uName.length < 2) {
            showAlert('usersFormErrors', 'Username is required', 'danger', true)
        } else if (!pass || pass.length < 2) {
            showAlert('usersFormErrors', 'Password is required', 'danger', true)
        } else if (!passC || passC.length < 2) {
            showAlert('usersFormErrors', 'Password must be confirmed', 'danger', true)
        } else if (pass !== passC) {
            showAlert('usersFormErrors', 'Password and it`s confirmation does not match', 'danger', true)
        }
    }
}

function cancelAddUser() {
    document.getElementById('newUser').value = ''
    document.getElementById('newPass').value = ''
    document.getElementById('newPassConfirm').value = ''
    document.getElementById('collapseOne').classList.remove('show')
}