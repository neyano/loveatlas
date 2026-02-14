import { ref } from 'vue';
import axios from 'axios';

/**
 * useQuotes - セリフ関連の共通ロジック
 * いいね・お気に入りのトグル処理を提供
 */
export function useQuotes() {
  const loading = ref(false);
  const error = ref(null);

  const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
  };

  /**
   * いいねのトグル
   * @param {number} quoteId - セリフID
   * @returns {Promise<{liked: boolean, likes_count: number}|null>}
   */
  const toggleLike = async (quoteId) => {
    loading.value = true;
    error.value = null;
    try {
      const { data } = await axios.post(`/quotes/${quoteId}/vote`);
      return data;
    } catch (err) {
      if (err.response?.status === 401) {
        window.location.href = '/login';
        return null;
      }
      error.value = err.response?.data?.message || 'いいねの処理に失敗しました';
      return null;
    } finally {
      loading.value = false;
    }
  };

  /**
   * お気に入りのトグル
   * @param {number} quoteId - セリフID
   * @param {boolean} isFavorited - 現在のお気に入り状態
   * @param {number|null} favoriteId - お気に入りレコードID（削除時用）
   * @returns {Promise<{added: boolean, favorite: object|null}|null>}
   */
  const toggleFavorite = async (quoteId, isFavorited, favoriteId) => {
    loading.value = true;
    error.value = null;
    try {
      if (isFavorited && favoriteId) {
        await axios.delete(`/favorites/${favoriteId}`);
        return { added: false, favorite: null };
      }
      const { data } = await axios.post('/favorites', {
        quote_id: quoteId,
      });
      return { added: true, favorite: data };
    } catch (err) {
      if (err.response?.status === 401) {
        window.location.href = '/login';
        return null;
      }
      error.value =
        err.response?.data?.message || 'お気に入りの処理に失敗しました';
      return null;
    } finally {
      loading.value = false;
    }
  };

  return {
    loading,
    error,
    toggleLike,
    toggleFavorite,
  };
}
