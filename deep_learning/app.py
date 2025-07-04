from api import Flask, request, jsonify
import numpy as np
import tensorflow as tf
import joblib

# Load autoencoder & encoder
autoencoder = tf.keras.models.load_model('model/autoencoder_berat03.keras')
encoder = tf.keras.models.Model(
    inputs=autoencoder.input,
    outputs=autoencoder.get_layer(index=2).output
)

# Load KMeans dan label klaster
kmeans = joblib.load('model/kmeans_berat03.pkl')
klaster_labels = joblib.load('model/klaster_labels.pkl')  # Ini dictionary klaster → label

app = Flask(__name__)

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.json
        berat = float(data['berat'])
        intensitas = float(data['intensitas'])

        # Prediksi
        X_input = np.array([[berat, intensitas]], dtype=np.float32)
        encoded = encoder.predict(X_input)
        klaster = int(kmeans.predict(encoded)[0])

        # Ambil label langsung dari hasil klaster
        kategori, kategori_intensitas = klaster_labels.get(klaster, ('Tidak Diketahui', 'Tidak Diketahui'))

        return jsonify({
            'klaster': klaster,
            'kategori': kategori,
            'kategori_intensitas': kategori_intensitas
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 400

if __name__ == '__main__':
    app.run(debug=True, port=5000)
