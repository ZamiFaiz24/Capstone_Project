from flask import Flask, request, jsonify
import numpy as np
import pandas as pd
import tensorflow as tf
import joblib


# Inisialisasi Flask
app = Flask(__name__)

# Load model dan scaler
autoencoder = tf.keras.models.load_model("model_mlp_sortir_telur.keras")
scaler = joblib.load("scaler_mlp.pkl")

# Label mapping (ubah sesuai label kamu)
label_mapping = {
    0: "Bagus Besar",
    1: "Buruk"
}

@app.route("/predict", methods=["POST"])
def predict():
    data = request.json  # Terima JSON: {"berat": 70.3, "intensitas": 12.4}
    
    try:
        berat = float(data["berat"])
        intensitas = float(data["intensitas"])
    except:
        return jsonify({"error": "Format data salah"}), 400

    # Preprocessing
    X = scaler.transform([[berat, intensitas]])

    # Prediksi
    pred = autoencoder.predict(X)
    label_index = np.argmax(pred)
    label = label_mapping[label_index]

    return jsonify({
        "label": label,
        "confidence": float(np.max(pred))
    })

if __name__ == "__main__":
    app.run(debug=True)
