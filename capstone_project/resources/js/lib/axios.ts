import axios from "axios";

const api = axios.create({
  baseURL: "/api", // otomatis mengarah ke laravel/api.php
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

export default api;
