export const generateTanggalRange = (startDate: string, days: number): string[] => {
  const dates = []
  const start = new Date(startDate)
  for (let i = 0; i < days; i++) {
    const d = new Date(start)
    d.setDate(start.getDate() + i)
    dates.push(d.toISOString().split('T')[0]) // Format 'YYYY-MM-DD'
  }
  return dates
}

export const fetchDataAktual = async (tanggalArray: string[]) => {
  // Contoh dummy data
  return tanggalArray.map(() => Math.floor(Math.random() * 5000) + 1000)
}

export const fetchDataPrediksi = async (tanggalArray: string[]) => {
  // Contoh dummy data prediksi
  return tanggalArray.map(() => Math.floor(Math.random() * 5000) + 1500)
}
