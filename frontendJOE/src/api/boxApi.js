import axios from 'axios';

const boxApi = axios.create({
    // Asegúrate de que no haya espacios y que la IP sea la correcta
    baseURL: 'http://192.168.1.77:8080/Box-TioVe/backend/public/index.php'
});

export default boxApi;