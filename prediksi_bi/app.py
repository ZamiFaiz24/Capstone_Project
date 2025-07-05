import joblib
from flask import Flask, jsonify, request
import pandas as pd
import joblib

# Load model yang sudah disimpan
model = joblib.load('prophet_model.pkl')

# Buat data tanggal ke depan & prediksi
future = model.make_future_dataframe(periods=30)
forecast = model.predict(future)

app = Flask(__name__)

@app.route("/api/prediksi-harga")
def prediksi():
    # ✅ Ambil parameter days dari query string (default 7 jika tidak dikirim)
    days = int(request.args.get("days", 7))

    # Load model & generate prediksi
    model = joblib.load("prophet_model.pkl")
    future = model.make_future_dataframe(periods=days)
    forecast = model.predict(future)

    # Ambil hanya hasil prediksi setelah data historis
    latest = model.history['ds'].max()
    prediksi = forecast[forecast['ds'] > latest][['ds', 'yhat']]

    # Format hasil prediksi
    prediksi['ds'] = prediksi['ds'].dt.strftime('%Y-%m-%d')
    result = prediksi.to_dict(orient='records')

    return jsonify(result)

if __name__ == "__main__":
    app.run(debug=True)