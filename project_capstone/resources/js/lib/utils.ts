import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import axios from 'axios'

const instance = axios.create({
  baseURL: 'http://localhost:8000/api', // Ganti kalau API-mu beda
  headers: {
    'Content-Type': 'application/json',
  },
})

export default instance

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}
