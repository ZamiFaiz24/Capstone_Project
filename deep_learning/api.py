from flask import Flask, request, jsonify
import numpy as np
import pandas as pd
import tensorflow as tf
import joblib

# === Load Encoder dari Autoencoder ===
autoencoder = tf.keras.models.load_model('model/autoencoder_model2.keras')
autoencoder.summary()
encoder = tf.keras.models.Model(
    inputs=autoencoder.input,
    outputs=autoencoder.get_layer('dense_44').output
)

# === Load Scaler dan Model MLP ===
scaler = joblib.load('model/scaler2.pkl')
mlp_model = joblib.load('model/mlp_model2.pkl') 

# === Mapping dari label hasil prediksi ke istilah akhir ===
label_mapping = {
    "Bagus Besar": "Besar Mutu Tinggi",
    "Bagus Kecil": "Kecil Mutu Tinggi",
    "Buruk": "Mutu Rendah"
}

# === Flask App ===
app = Flask(__name__)

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.json
        berat = float(data['berat'])
        intensitas = float(data['intensitas'])

        # Buat DataFrame input dengan kolom sesuai saat training
        X_input = pd.DataFrame([[berat, intensitas]], columns=['berat', 'intensitas'])

        # Scaling
        X_scaled = scaler.transform(X_input)

        # Encoding dari autoencoder
        X_encoded = encoder.predict(X_scaled)

        # Prediksi kelas pakai predict_proba kalau ada
        if hasattr(mlp_model, "predict_proba"):
            y_pred_proba = mlp_model.predict_proba(X_encoded)
            y_pred_class = np.argmax(y_pred_proba, axis=1)[0]
        else:
            y_pred_class = mlp_model.predict(X_encoded)[0]

        # Mapping label
        index_to_label = {0: "Bagus Besar", 1: "Bagus Kecil", 2: "Buruk"}
        label_awal = index_to_label.get(y_pred_class, "Tidak Dikenal")

        label_mapping = {
            "Bagus Besar": "Besar Mutu Tinggi",
            "Bagus Kecil": "Kecil Mutu Tinggi",
            "Buruk": "Mutu Rendah"
        }
        label_akhir = label_mapping.get(label_awal, "Tidak Dikenal")

        # Pisahkan label akhir
        if label_akhir == "Besar Mutu Tinggi":
            ukuran = "Besar"
            mutu = "Mutu Tinggi"
        elif label_akhir == "Kecil Mutu Tinggi":
            ukuran = "Kecil"
            mutu = "Mutu Tinggi"
        elif label_akhir == "Mutu Rendah":
            ukuran = "Tidak Tersaring"
            mutu = "Mutu Rendah"
        else:
            ukuran = "Unknown"
            mutu = "Unknown"

        return jsonify({
            'klaster': int(y_pred_class),
            'label_prediksi': label_awal,
            'label_tampilan': label_akhir,
            'label_ukuran': ukuran,
            'label_kualitas': mutu
        })

    except Exception as e:
        print(f"[ERROR] {e}")
        return jsonify({'error': str(e)}), 400

if __name__ == '__main__':
    app.run(debug=True, port=5001)
